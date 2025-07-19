<?php

namespace App\Controller;

use App\Entity\Car;
use App\Entity\Carpooling;
use App\Entity\CarpoolingUser;
use App\Entity\User;
use App\Enum\CarpoolingStatus;
use OpenApi\Attributes as OA;
use App\Repository\CarpoolingRepository;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use DateTime;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('api/carpoolings', name: 'app_api_carpooling_')]
final class CarpoolingController extends AbstractController
{
    private int $platformUserId;

    public function __construct(
        private EntityManagerInterface $manager,
        private CarpoolingRepository $repository,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator,
        private NormalizerInterface $normalizer,
        private Security $security,
        private UserRepository $userRepository,
        ParameterBagInterface $params
    ) {
        // Retrieve platform user ID from parameters
        $this->platformUserId = $params->get('platform_user_id');
    }

    // =========================================================================
    // I. Carpooling Listing Routes
    // =========================================================================

    /**
     * ////////////////////////////////////////////////////////////////////////
     * /// ROUTE: List All Carpoolings
     * /// FUNCTION: Fetches and returns a list of all carpoolings.
     * ////////////////////////////////////////////////////////////////////////
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function listAllCarpoolings(): JsonResponse
    {
        $carpoolings = $this->repository->findAll();

        return $this->json($carpoolings, Response::HTTP_OK, [], [
            'groups' => 'carpooling:read',
        ]);
    }

    /**
     * ////////////////////////////////////////////////////////////////////////
     * /// ROUTE: List Carpoolings by User
     * /// FUNCTION: Fetches and returns a list of carpoolings associated with the authenticated user (as driver or passenger).
     * ////////////////////////////////////////////////////////////////////////
     */
    #[Route('/list-by-user', name: 'list_by_user', methods: ['GET'])]
    public function listCarpoolingsByUser(): JsonResponse
    {
        $user = $this->security->getUser();

        if (!$user) {
            return new JsonResponse(['message' => 'Authentication required to access your carpoolings.'], Response::HTTP_UNAUTHORIZED);
        }

        $carpoolings = $this->repository->findByUser($user);

        return $this->json($carpoolings, Response::HTTP_OK, [], [
            'groups' => [
                'carpooling:read',
                'car:read',
                'brand:read',
                'user:read'
            ],
        ]);
    }

    // =========================================================================
    // II. Carpooling Management Routes
    // =========================================================================

    /**
     * ////////////////////////////////////////////////////////////////////////
     * /// ROUTE: Create New Carpooling
     * /// FUNCTION: Creates a new carpooling entry.
     * ////////////////////////////////////////////////////////////////////////
     */
    #[Route('', name: 'new', methods: 'POST')]
    public function new(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $carpooling = new Carpooling();

        // Validate and set Departure Date
        if (isset($data['departureDate'])) {
            $departureDate = DateTime::createFromFormat('Y-m-d', $data['departureDate']);
            if ($departureDate === false) {
                return new JsonResponse(['message' => 'Invalid departureDate format. Expected YYYY-MM-DD.'], Response::HTTP_BAD_REQUEST);
            }
            $carpooling->setDepartureDate($departureDate);
        } else {
            return new JsonResponse(['message' => 'departureDate is required.'], Response::HTTP_BAD_REQUEST);
        }

        // Validate and set Departure Time
        if (isset($data['departureTime'])) {
            $departureTime = DateTime::createFromFormat('H:i:s', $data['departureTime']);
            if ($departureTime === false) {
                return new JsonResponse(['message' => 'Invalid departureTime format. Expected HH:MM:SS.'], Response::HTTP_BAD_REQUEST);
            }
            $carpooling->setDepartureTime($departureTime);
        } else {
            return new JsonResponse(['message' => 'departureTime is required.'], Response::HTTP_BAD_REQUEST);
        }

        // Validate and set Arrival Date
        if (isset($data['arrivalDate'])) {
            $arrivalDate = DateTime::createFromFormat('Y-m-d', $data['arrivalDate']);
            if ($arrivalDate === false) {
                return new JsonResponse(['message' => 'Invalid arrivalDate format. Expected YYYY-MM-DD.'], Response::HTTP_BAD_REQUEST);
            }
            $carpooling->setArrivalDate($arrivalDate);
        } else {
            return new JsonResponse(['message' => 'arrivalDate is required.'], Response::HTTP_BAD_REQUEST);
        }

        // Validate and set Arrival Time
        if (isset($data['arrivalTime'])) {
            $arrivalTime = DateTime::createFromFormat('H:i:s', $data['arrivalTime']);
            if ($arrivalTime === false) {
                return new JsonResponse(['message' => 'Invalid arrivalTime format. Expected HH:MM:SS.'], Response::HTTP_BAD_REQUEST);
            }
            $carpooling->setArrivalTime($arrivalTime);
        } else {
            return new JsonResponse(['message' => 'arrivalTime is required.'], Response::HTTP_BAD_REQUEST);
        }

        // Set Car
        $car = $this->manager->getRepository(Car::class)->find($data['car']);
        if (!$car) {
            return new JsonResponse(['message' => 'Car not found'], Response::HTTP_BAD_REQUEST);
        }
        $carpooling->setCar($car);

        // Set creation timestamp and initial status
        $carpooling->setCreatedAt(new DateTimeImmutable());
        $carpooling->setStatus(CarpoolingStatus::OPEN);

        // Set other properties
        $carpooling->setDeparturePlace($data['departurePlace'] ?? null);
        $carpooling->setArrivalPlace($data['arrivalPlace'] ?? null);
        $carpooling->setSeatCount($data['availableSeats'] ?? null); // Initial seat count
        $carpooling->setPricePerPerson($data['pricePerPassenger'] ?? null);

        // Determine if carpooling is eco-friendly
        if ($car->getEnergy() === 'Électrique') {
            $carpooling->setIsEco(true);
        } else {
            $carpooling->setIsEco($data['isEco'] ?? false);
        }

        // Set status, defaulting to OPEN
        $carpooling->setStatus(CarpoolingStatus::tryFrom($data['status'] ?? CarpoolingStatus::OPEN->value));

        // Get current authenticated user (driver)
        $user = $this->security->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'User not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        // Create CarpoolingUser entry for the driver
        $carpoolingUser = new CarpoolingUser();
        $carpoolingUser->setUser($user);
        $carpoolingUser->setCarpooling($carpooling);
        $carpoolingUser->setIsDriver(true);

        $this->manager->persist($carpooling);
        $this->manager->persist($carpoolingUser);
        $this->manager->flush();

        $responseData = $this->serializer->normalize($carpooling, 'json', [
            'groups' => 'carpooling:read',
        ]);

        $location = $this->urlGenerator->generate(
            name: 'app_api_carpooling_show',
            parameters: ['id' => $carpooling->getId()],
            referenceType: UrlGeneratorInterface::ABSOLUTE_URL
        );

        return new JsonResponse(
            data: $responseData,
            status: Response::HTTP_CREATED,
            headers: ["Location" => $location]
        );
    }

    /**
     * ////////////////////////////////////////////////////////////////////////
     * /// ROUTE: Show Carpooling Details
     * /// FUNCTION: Retrieves a single carpooling by its ID.
     * ////////////////////////////////////////////////////////////////////////
     */
    #[Route('/{id}', name: 'show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $carpooling = $this->repository->find($id);

        if ($carpooling) {
            $responseData = $this->serializer->normalize($carpooling, 'json', [
                'groups' => [
                    'carpooling:read',
                    'car:read',
                    'brand:read',
                    'user:read'
                ],
            ]);
            return new JsonResponse($responseData, status: Response::HTTP_OK);
        }

        return new JsonResponse(data: null, status: Response::HTTP_NOT_FOUND);
    }

    /**
     * ////////////////////////////////////////////////////////////////////////
     * /// ROUTE: Search Carpoolings
     * /// FUNCTION: Searches for carpoolings based on departure place, arrival place, and departure date.
     * ////////////////////////////////////////////////////////////////////////
     */
    #[Route('/search', name: 'app_carpooling_search', methods: ['GET'])]
    public function search(Request $request, CarpoolingRepository $repository): JsonResponse
    {
        $departurePlace = $request->query->get('departurePlace');
        $arrivalPlace = $request->query->get('arrivalPlace');
        $departureDateString = $request->query->get('departureDate');
        $departureDate = null;

        if ($departureDateString) {
            try {
                $departureDate = new \DateTimeImmutable($departureDateString);
            } catch (\Exception $e) {
                return new JsonResponse(['error' => 'Invalid departureDate format'], Response::HTTP_BAD_REQUEST);
            }
        }

        $results = $repository->findBySearchCriteria($departurePlace, $arrivalPlace, $departureDate);

        return $this->json($results, Response::HTTP_OK, [], [
            'groups' => 'carpooling:read',
        ]);
    }

    /**
     * ////////////////////////////////////////////////////////////////////////
     * /// ROUTE: Edit Carpooling
     * /// FUNCTION: Updates an existing carpooling.
     * ////////////////////////////////////////////////////////////////////////
     */
    #[Route('/{id}', name: 'edit', requirements: ['id' => '\d+'], methods: ['PUT'])]
    public function edit(int $id, Request $request): JsonResponse
    {
        $carpooling = $this->repository->find($id);

        if (!$carpooling) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        // Update departure date and time
        if (isset($data['departureDate']) && isset($data['departureTime'])) {
            $departureDateTime = DateTime::createFromFormat('Y-m-d H:i:s', $data['departureDate'] . ' ' . $data['departureTime']);
            if ($departureDateTime !== false) {
                $carpooling->setDepartureDate($departureDateTime);
                $carpooling->setDepartureTime($departureDateTime);
            }
        }
        // Update arrival date and time
        if (isset($data['arrivalDate']) && isset($data['arrivalTime'])) {
            $arrivalDateTime = DateTime::createFromFormat('Y-m-d H:i:s', $data['arrivalDate'] . ' ' . $data['arrivalTime']);
            if ($arrivalDateTime !== false) {
                $carpooling->setArrivalDate($arrivalDateTime);
                $carpooling->setArrivalTime($arrivalDateTime);
            }
        }

        // Update other properties if provided
        $carpooling->setDeparturePlace($data['departurePlace'] ?? $carpooling->getDeparturePlace());
        $carpooling->setArrivalPlace($data['arrivalPlace'] ?? $carpooling->getArrivalPlace());
        $carpooling->setPricePerPerson($data['pricePerPassenger'] ?? $carpooling->getPricePerPerson());
        $carpooling->setSeatCount($data['availableSeats'] ?? $carpooling->getSeatCount()); // Allow updating available seats

        // Recalculate isEco based on car energy or provided value
        $car = $carpooling->getCar();
        if ($car && $car->getEnergy() === 'Électrique') {
            $carpooling->setIsEco(true);
        } else {
            $carpooling->setIsEco($data['isEco'] ?? $carpooling->getIsEco());
        }

        $this->manager->flush();

        $responseData = $this->serializer->normalize($carpooling, 'json', [
            'groups' => 'carpooling:read',
        ]);

        return new JsonResponse($responseData, status: Response::HTTP_OK);
    }

    /**
     * ////////////////////////////////////////////////////////////////////////
     * /// ROUTE: Delete Carpooling
     * /// FUNCTION: Deletes a carpooling. Accessible only to ADMIN role.
     * ////////////////////////////////////////////////////////////////////////
     */
    #[Route('/{id}', name: 'delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(int $id): JsonResponse
    {
        $carpooling = $this->repository->find($id);

        if (!$carpooling) {
            return new JsonResponse(['message' => 'Carpooling not found.'], Response::HTTP_NOT_FOUND);
        }

        // When a carpooling is deleted, ensure associated CarpoolingUser entries are handled
        // Doctrine's CascadePersist/Remove might handle this automatically if configured,
        // otherwise, you might need to remove them explicitly here before removing carpooling.

        $this->manager->remove($carpooling);
        $this->manager->flush();

        return new JsonResponse(['message' => 'Carpooling deleted successfully.'], Response::HTTP_NO_CONTENT);
    }

    // =========================================================================
    // III. Carpooling Participation & Cancellation Routes
    // =========================================================================

    /**
     * ////////////////////////////////////////////////////////////////////////
     * /// ROUTE: Participate in Carpooling
     * /// FUNCTION: Handles user participation in a carpooling (as a passenger),
     * /// managing credit deductions and allocations, and updating seat count.
     * ////////////////////////////////////////////////////////////////////////
     */
    #[Route('/{id}/participate', name: 'participate', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function participate(Carpooling $carpooling, #[CurrentUser] User $user, EntityManagerInterface $entityManager): JsonResponse
    {
        // Check if the user is the driver
        foreach ($carpooling->getCarpoolingUsers() as $carpoolingUser) {
            if ($carpoolingUser->getUser() === $user && $carpoolingUser->isDriver()) {
                return new JsonResponse(['message' => 'You are the driver of this carpooling, you cannot join as a passenger.'], Response::HTTP_BAD_REQUEST);
            }
        }

        // Check if there are available seats BEFORE processing participation
        if ($carpooling->getSeatCount() <= 0) {
            return new JsonResponse(
                ['success' => false, 'message' => 'No available seats left for this carpooling.'],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        // 1. Find an existing CarpoolingUser entity for this user and carpooling
        $carpoolingUser = $entityManager->getRepository(CarpoolingUser::class)->findOneBy([
            'user' => $user,
            'carpooling' => $carpooling,
        ]);

        if ($carpoolingUser) {
            // An existing CarpoolingUser entry was found.
            if ($carpoolingUser->isCancelled()) {
                // If the existing participation was cancelled, reactivate it.
                $carpoolingUser->setIsCancelled(false);
                $message = 'Your participation has been reactivated successfully!';
                // Decrement seat count when re-activating
                $carpooling->setSeatCount($carpooling->getSeatCount() - 1);
                $entityManager->persist($carpooling); // Persist carpooling change
            } else {
                // If the existing participation was not cancelled, the user is already active.
                return new JsonResponse(
                    ['success' => false, 'message' => 'You are already participating in this carpooling.'],
                    JsonResponse::HTTP_CONFLICT
                );
            }
        } else {
            // No existing CarpoolingUser entry. This is a new participation.
            $carpoolingUser = new CarpoolingUser();
            $carpoolingUser->setUser($user);
            $carpoolingUser->setCarpooling($carpooling);
            $carpoolingUser->setIsDriver(false); // By default, a participant is a passenger
            $carpoolingUser->setIsCancelled(false); // New participation is active

            $entityManager->persist($carpoolingUser); // Only persist if it's a new entity
            $message = 'Participation successful!';

            // Decrement seat count for new participation
            $carpooling->setSeatCount($carpooling->getSeatCount() - 1);
            $entityManager->persist($carpooling); // Persist carpooling change
        }

        // --- Credit deduction logic ---
        $pricePerPerson = $carpooling->getPricePerPerson();
        $platformFee = 2; // Fixed platform fee

        if ($user->getCredits() < $pricePerPerson) {
            return new JsonResponse(
                ['success' => false, 'message' => 'Insufficient credits to participate in this carpooling.'],
                JsonResponse::HTTP_PAYMENT_REQUIRED // Changed from BAD_REQUEST for more specific error
            );
        }

        $user->setCredits($user->getCredits() - $pricePerPerson);
        $entityManager->persist($user);

        // Get the platform user
        /** @var User|null $platformUser */
        $platformUser = $this->userRepository->find($this->platformUserId);
        if (!$platformUser) {
            return new JsonResponse(['message' => 'Internal error: Platform user not found.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Credit platform fee to platform user
        $platformUser->setCredits($platformUser->getCredits() + $platformFee);
        $entityManager->persist($platformUser);

        // Credit remaining amount to the driver
        /** @var User|null $driver */
        $driver = null;
        foreach ($carpooling->getCarpoolingUsers() as $driverCarpoolingUser) {
            if ($driverCarpoolingUser->isDriver()) {
                $driver = $driverCarpoolingUser->getUser();
                break;
            }
        }

        if (!$driver) {
            return new JsonResponse(['message' => 'Internal error: Driver not found for this carpooling.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $driverReceiveAmount = $pricePerPerson - $platformFee;
        if ($driverReceiveAmount < 0) {
            $driverReceiveAmount = 0;
        }

        $driver->setCredits($driver->getCredits() + $driverReceiveAmount);
        $entityManager->persist($driver);
        // --- End credit deduction logic ---

        $entityManager->flush(); // Execute database changes (INSERT or UPDATE)

        return new JsonResponse(
            ['success' => true, 'message' => $message, 'newCredits' => $user->getCredits(), 'remainingSeats' => $carpooling->getSeatCount()],
            JsonResponse::HTTP_OK
        );
    }

    /**
     * ////////////////////////////////////////////////////////////////////////
     * /// ROUTE: Cancel Carpooling
     * /// FUNCTION: Handles cancellation of a carpooling by the driver,
     * /// refunding all passengers and adjusting credits.
     * ////////////////////////////////////////////////////////////////////////
     */
    #[Route('/{id}/cancel', name: 'cancel', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function cancel(int $id): JsonResponse
    {
        $this->manager->getConnection()->beginTransaction();

        try {
            $carpooling = $this->repository->find($id);

            if (!$carpooling) {
                $this->manager->getConnection()->rollBack();
                return $this->json(['message' => 'Carpooling not found.'], Response::HTTP_NOT_FOUND);
            }

            /** @var User $currentUser */
            $currentUser = $this->security->getUser();
            if (!$currentUser) {
                $this->manager->getConnection()->rollBack();
                return $this->json(['message' => 'Authentication required.'], Response::HTTP_UNAUTHORIZED);
            }

            // Verify current user is the driver of this carpooling
            $isDriver = false;
            foreach ($carpooling->getCarpoolingUsers() as $carpoolingUser) {
                if ($carpoolingUser->isDriver() && $carpoolingUser->getUser()->getId() === $currentUser->getId()) {
                    $isDriver = true;
                    break;
                }
            }

            if (!$isDriver) {
                $this->manager->getConnection()->rollBack();
                return $this->json(['message' => 'You are not the driver of this carpooling.'], Response::HTTP_FORBIDDEN);
            }

            // Prevent cancellation if carpooling is already cancelled or completed
            if ($carpooling->getStatus() === CarpoolingStatus::CANCELLED || $carpooling->getStatus() === CarpoolingStatus::COMPLETED) {
                $this->manager->getConnection()->rollBack();
                return $this->json(['message' => 'This carpooling cannot be cancelled.'], Response::HTTP_BAD_REQUEST);
            }

            $pricePerPerson = $carpooling->getPricePerPerson();
            $platformCommission = 2; // Fixed platform fee

            /** @var User|null $driver */
            $driver = null;
            foreach ($carpooling->getCarpoolingUsers() as $driverCarpoolingUser) {
                if ($driverCarpoolingUser->isDriver()) {
                    $driver = $driverCarpoolingUser->getUser();
                    break;
                }
            }

            if (!$driver) {
                $this->manager->getConnection()->rollBack();
                error_log('Error: Driver not found for carpooling during cancellation.');
                return $this->json(['message' => 'Internal error: Driver not found for this carpooling.'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            // Refund active passengers and adjust platform/driver credits
            $passengersRefundedCount = 0;
            foreach ($carpooling->getCarpoolingUsers() as $carpoolingUser) {
                // Only process active passengers who paid
                if (!$carpoolingUser->isDriver() && !$carpoolingUser->isCancelled()) {
                    /** @var User $passenger */
                    $passenger = $carpoolingUser->getUser();

                    // Refund passenger: give back the amount they paid
                    $refundAmount = $pricePerPerson;
                    $passenger->setCredits($passenger->getCredits() + $refundAmount);
                    $this->manager->persist($passenger);

                    // Deduct platform's commission (if already received for this passenger)
                    /** @var User|null $platformUser */
                    $platformUser = $this->userRepository->find($this->platformUserId);
                    if ($platformUser) {
                        // Ensure platform has enough credits before debiting
                        if ($platformUser->getCredits() >= $platformCommission) {
                            $platformUser->setCredits($platformUser->getCredits() - $platformCommission);
                            $this->manager->persist($platformUser);
                        } else {
                            // Log and potentially roll back if platform cannot cover the refund
                            error_log('Error: Platform has insufficient credits to be debited for passenger refund during carpooling cancellation.');
                            $this->manager->getConnection()->rollBack();
                            return $this->json(['message' => 'Failed to refund: Platform has insufficient credits.'], Response::HTTP_INTERNAL_SERVER_ERROR);
                        }
                    } else {
                        error_log('Error: Platform user not found during mass refund.');
                        $this->manager->getConnection()->rollBack();
                        return $this->json(['message' => 'Internal error: Platform user not found during refund process.'], Response::HTTP_INTERNAL_SERVER_ERROR);
                    }

                    // Deduct driver's received amount
                    $driverDeductAmount = $pricePerPerson - $platformCommission;
                    if ($driverDeductAmount < 0) $driverDeductAmount = 0; // Should not be negative

                    // Ensure driver has enough credits to be debited
                    if ($driver->getCredits() >= $driverDeductAmount) {
                        $driver->setCredits($driver->getCredits() - $driverDeductAmount);
                        $this->manager->persist($driver);
                    } else {
                        // Critical scenario: Driver might not have enough credits if they already spent them.
                        error_log('Error: Driver has insufficient credits to be debited for passenger refund during carpooling cancellation.');
                        $this->manager->getConnection()->rollBack();
                        return $this->json(['message' => 'Failed to refund: Driver has insufficient credits to cover passenger reimbursement.'], Response::HTTP_BAD_REQUEST);
                    }

                    // Mark CarpoolingUser as cancelled
                    $carpoolingUser->setIsCancelled(true);
                    $this->manager->persist($carpoolingUser);
                    $passengersRefundedCount++;
                }
            }

            // Update carpooling status to cancelled
            $carpooling->setStatus(CarpoolingStatus::CANCELLED);
            // When carpooling is cancelled by driver, all seats become "available" in theory,
            // but the carpooling is no longer open for booking.
            // Resetting seatCount to the car's capacity or 0 depends on business logic.
            // For now, if cancelled, it's not available for booking, so seatCount won't be modified here to reflect "open" seats.
            $this->manager->persist($carpooling);

            $this->manager->flush();
            $this->manager->getConnection()->commit();

            return $this->json(['message' => 'Carpooling cancelled and ' . $passengersRefundedCount . ' passenger(s) refunded successfully.'], Response::HTTP_OK);
        } catch (\Exception $e) {
            $this->manager->getConnection()->rollBack();
            error_log('Error cancelling carpooling: ' . $e->getMessage());
            return $this->json(['message' => 'An error occurred during carpooling cancellation.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * ////////////////////////////////////////////////////////////////////////
     * /// ROUTE: Leave Carpooling
     * /// FUNCTION: Handles a passenger leaving a carpooling,
     * /// refunding their credits and adjusting driver/platform credits, and updating seat count.
     * ////////////////////////////////////////////////////////////////////////
     */
    #[Route('/{id}/leave', name: 'leave', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function leaveCarpooling(int $id): JsonResponse
    {
        $this->manager->getConnection()->beginTransaction();

        try {
            $carpooling = $this->repository->find($id);

            if (!$carpooling) {
                $this->manager->getConnection()->rollBack();
                return $this->json(['message' => 'Carpooling not found.'], Response::HTTP_NOT_FOUND);
            }

            /** @var User $currentUser */
            $currentUser = $this->security->getUser();
            if (!$currentUser) {
                $this->manager->getConnection()->rollBack();
                return $this->json(['message' => 'Authentication required.'], Response::HTTP_UNAUTHORIZED);
            }

            // Find the CarpoolingUser entry for the current user in this carpooling
            $carpoolingUserToLeave = null;
            foreach ($carpooling->getCarpoolingUsers() as $carpoolingUser) {
                if ($carpoolingUser->getUser()->getId() === $currentUser->getId()) {
                    $carpoolingUserToLeave = $carpoolingUser;
                    break;
                }
            }

            if (!$carpoolingUserToLeave) {
                $this->manager->getConnection()->rollBack();
                return $this->json(['message' => 'You are not participating in this carpooling.'], Response::HTTP_NOT_FOUND);
            }

            // Prevent driver from using 'leave' endpoint; they must 'cancel' the carpooling
            if ($carpoolingUserToLeave->isDriver()) {
                $this->manager->getConnection()->rollBack();
                return $this->json(['message' => 'As a driver, you cannot "leave" the carpooling. You must cancel it entirely.'], Response::HTTP_BAD_REQUEST);
            }

            // Prevent leaving if participation is already cancelled
            if ($carpoolingUserToLeave->isCancelled()) {
                $this->manager->getConnection()->rollBack();
                return $this->json(['message' => 'Your participation in this carpooling is already cancelled.'], Response::HTTP_BAD_REQUEST);
            }

            // Prevent leaving if carpooling is completed or cancelled by driver
            if ($carpooling->getStatus() === CarpoolingStatus::COMPLETED || $carpooling->getStatus() === CarpoolingStatus::CANCELLED) {
                $this->manager->getConnection()->rollBack();
                return $this->json(['message' => 'Cannot leave a completed or already cancelled carpooling.'], Response::HTTP_BAD_REQUEST);
            }

            $pricePerPerson = $carpooling->getPricePerPerson();
            $platformFee = 2; // Fixed platform fee

            // 1. Refund passenger: Add back the full amount they initially paid (pricePerPerson)
            $currentUser->setCredits($currentUser->getCredits() + $pricePerPerson);
            $this->manager->persist($currentUser);

            // 2. Deduct platform's commission: Platform returns the fee it received
            /** @var User|null $platformUser */
            $platformUser = $this->userRepository->find($this->platformUserId);
            if ($platformUser) {
                // Ensure platform has enough credits before debiting
                if ($platformUser->getCredits() >= $platformFee) {
                    $platformUser->setCredits($platformUser->getCredits() - $platformFee);
                    $this->manager->persist($platformUser);
                } else {
                    error_log('Error: Platform has insufficient credits to be debited for passenger cancellation refund.');
                    $this->manager->getConnection()->rollBack();
                    return $this->json(['message' => 'Internal error: Platform has insufficient credits for refund.'], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            } else {
                error_log('Error: Platform user not found during passenger cancellation refund.');
                $this->manager->getConnection()->rollBack();
                return $this->json(['message' => 'Internal error: Platform user not found during refund process.'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            // 3. Deduct from driver: Driver returns the amount they received from this passenger
            /** @var User|null $driver */
            $driver = null;
            foreach ($carpooling->getCarpoolingUsers() as $driverCarpoolingUser) {
                if ($driverCarpoolingUser->isDriver()) {
                    $driver = $driverCarpoolingUser->getUser();
                    break;
                }
            }

            if ($driver) {
                $driverReceivedAmount = $pricePerPerson - $platformFee;
                if ($driverReceivedAmount < 0) $driverReceivedAmount = 0;

                // Ensure driver has enough credits to be debited
                if ($driver->getCredits() >= $driverReceivedAmount) {
                    $driver->setCredits($driver->getCredits() - $driverReceivedAmount);
                    $this->manager->persist($driver);
                } else {
                    // Critical scenario: Driver might not have enough credits if they already spent them.
                    error_log('Error: Driver has insufficient credits to be debited for passenger refund during carpooling cancellation.');
                    $this->manager->getConnection()->rollBack();
                    return $this->json(['message' => 'Failed to refund: Driver has insufficient credits to cover passenger reimbursement.'], Response::HTTP_BAD_REQUEST);
                }
            } else {
                error_log('Error: Driver not found for carpooling during passenger cancellation refund.');
                $this->manager->getConnection()->rollBack();
                return $this->json(['message' => 'Internal error: Driver not found for this carpooling during refund process.'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            // Mark CarpoolingUser as cancelled
            $carpoolingUserToLeave->setIsCancelled(true);
            $this->manager->persist($carpoolingUserToLeave);

            // Increment seat count when a passenger leaves
            $carpooling->setSeatCount($carpooling->getSeatCount() + 1);
            $this->manager->persist($carpooling); // Persist carpooling change

            $this->manager->flush();
            $this->manager->getConnection()->commit();

            return $this->json(
                ['success' => true, 'message' => 'You have successfully left the carpooling and have been refunded.', 'newCredits' => $currentUser->getCredits(), 'remainingSeats' => $carpooling->getSeatCount()],
                Response::HTTP_OK
            );
        } catch (\Exception $e) {
            $this->manager->getConnection()->rollBack();
            error_log('Error leaving carpooling: ' . $e->getMessage());
            return $this->json(['message' => 'An error occurred while leaving the carpooling.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
