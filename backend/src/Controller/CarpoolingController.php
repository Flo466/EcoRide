<?php

namespace App\Controller;

use App\Entity\Car;
use App\Entity\Carpooling;
use App\Entity\CarpoolingUser;
use App\Entity\User; // Assurez-vous que User est importé
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
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface; // Added for parameters

#[Route('api/carpoolings', name: 'app_api_carpooling_')]
final class CarpoolingController extends AbstractController
{
    private int $platformUserId; // Property to store platform user ID

    public function __construct(
        private EntityManagerInterface $manager,
        private CarpoolingRepository $repository,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator,
        private NormalizerInterface $normalizer,
        private Security $security,
        private UserRepository $userRepository,
        ParameterBagInterface $params // Inject ParameterBagInterface
    ) {
        // Get the platform user ID from parameters
        $this->platformUserId = $params->get('platform_user_id');
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function listAllCarpoolings(): JsonResponse
    {
        $carpoolings = $this->repository->findAll();

        return $this->json($carpoolings, Response::HTTP_OK, [], [
            'groups' => 'carpooling:read',
        ]);
    }

    /**
     * Lists all carpoolings for the authenticated user (as driver or passenger).
     */
    #[Route('/list-by-user', name: 'list_by_user', methods: ['GET'])]
    public function listCarpoolingsByUser(): JsonResponse
    {
        $user = $this->security->getUser();

        if (!$user) {
            return new JsonResponse(['message' => 'Authentification requise pour accéder à vos covoiturages.'], Response::HTTP_UNAUTHORIZED);
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


    #[Route('', name: 'new', methods: 'POST')]
    public function new(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $carpooling = new Carpooling();

        // Departure Date
        if (isset($data['departureDate'])) {
            $departureDate = DateTime::createFromFormat('Y-m-d', $data['departureDate']);
            if ($departureDate === false) {
                return new JsonResponse(['message' => 'Invalid departureDate format. Expected YYYY-MM-DD.'], Response::HTTP_BAD_REQUEST);
            }
            $carpooling->setDepartureDate($departureDate);
        } else {
            return new JsonResponse(['message' => 'departureDate is required.'], Response::HTTP_BAD_REQUEST);
        }

        // Departure Time
        if (isset($data['departureTime'])) {
            $departureTime = DateTime::createFromFormat('H:i:s', $data['departureTime']);
            if ($departureTime === false) {
                return new JsonResponse(['message' => 'Invalid departureTime format. Expected HH:MM:SS.'], Response::HTTP_BAD_REQUEST);
            }
            $carpooling->setDepartureTime($departureTime);
        } else {
            return new JsonResponse(['message' => 'departureTime is required.'], Response::HTTP_BAD_REQUEST);
        }

        // Arrival Date
        if (isset($data['arrivalDate'])) {
            $arrivalDate = DateTime::createFromFormat('Y-m-d', $data['arrivalDate']);
            if ($arrivalDate === false) {
                return new JsonResponse(['message' => 'Invalid arrivalDate format. Expected YYYY-MM-DD.'], Response::HTTP_BAD_REQUEST);
            }
            $carpooling->setArrivalDate($arrivalDate);
        } else {
            return new JsonResponse(['message' => 'arrivalDate is required.'], Response::HTTP_BAD_REQUEST);
        }

        // Arrival Time
        if (isset($data['arrivalTime'])) {
            $arrivalTime = DateTime::createFromFormat('H:i:s', $data['arrivalTime']);
            if ($arrivalTime === false) {
                return new JsonResponse(['message' => 'Invalid arrivalTime format. Expected HH:MM:SS.'], Response::HTTP_BAD_REQUEST);
            }
            $carpooling->setArrivalTime($arrivalTime);
        } else {
            return new JsonResponse(['message' => 'arrivalTime is required.'], Response::HTTP_BAD_REQUEST);
        }

        $car = $this->manager->getRepository(Car::class)->find($data['car']);
        if (!$car) {
            return new JsonResponse(['message' => 'Car not found'], Response::HTTP_BAD_REQUEST);
        }

        $carpooling->setCar($car);
        $carpooling->setCreatedAt(new DateTimeImmutable());
        $carpooling->setStatus(CarpoolingStatus::OPEN);

        $carpooling->setDeparturePlace($data['departurePlace'] ?? null);
        $carpooling->setArrivalPlace($data['arrivalPlace'] ?? null);
        $carpooling->setSeatCount($data['availableSeats'] ?? null);
        $carpooling->setPricePerPerson($data['pricePerPassenger'] ?? null);

        if ($car->getEnergy() === 'Électrique') {
            $carpooling->setIsEco(true);
        } else {
            $carpooling->setIsEco($data['isEco'] ?? false);
        }

        $carpooling->setStatus(CarpoolingStatus::tryFrom($data['status'] ?? CarpoolingStatus::OPEN->value));

        $user = $this->security->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'User not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

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
                return new JsonResponse(['error' => 'Invalid departureDate format'], 400);
            }
        }

        $results = $repository->findBySearchCriteria($departurePlace, $arrivalPlace, $departureDate);

        return $this->json($results, 200, [], [
            'groups' => 'carpooling:read',
        ]);
    }

    #[Route('/{id}', name: 'edit', requirements: ['id' => '\d+'], methods: ['PUT'])]
    public function edit(int $id, Request $request): JsonResponse
    {
        $carpooling = $this->repository->find($id);

        if (!$carpooling) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['departureDate']) && isset($data['departureTime'])) {
            $departureDateTime = DateTime::createFromFormat('Y-m-d H:i:s', $data['departureDate'] . ' ' . $data['departureTime']);
            if ($departureDateTime !== false) {
                $carpooling->setDepartureDate($departureDateTime);
                $carpooling->setDepartureTime($departureDateTime);
            }
        }
        if (isset($data['arrivalDate']) && isset($data['arrivalTime'])) {
            $arrivalDateTime = DateTime::createFromFormat('Y-m-d H:i:s', $data['arrivalDate'] . ' ' . $data['arrivalTime']);
            if ($arrivalDateTime !== false) {
                $carpooling->setArrivalDate($arrivalDateTime);
                $carpooling->setArrivalTime($arrivalDateTime);
            }
        }

        $carpooling->setDeparturePlace($data['departurePlace'] ?? $carpooling->getDeparturePlace());
        $carpooling->setArrivalPlace($data['arrivalPlace'] ?? $carpooling->getArrivalPlace());
        $carpooling->setPricePerPerson($data['pricePerPassenger'] ?? $carpooling->getPricePerPerson());

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
     * Handles user participation in a carpooling.
     */
    #[Route('/{id}/participate', name: 'participate', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function participate(int $id, Request $request): JsonResponse
    {
        $carpooling = $this->repository->find($id);

        if (!$carpooling) {
            return new JsonResponse(['message' => 'Covoiturage non trouvé.'], Response::HTTP_NOT_FOUND);
        }

        /** @var User $user */
        $user = $this->security->getUser();
        if (!$user) {
            return new JsonResponse(['message' => 'Authentification requise.'], Response::HTTP_UNAUTHORIZED);
        }

        // Check if the user is the driver
        foreach ($carpooling->getCarpoolingUsers() as $carpoolingUser) {
            if ($carpoolingUser->getUser() === $user && $carpoolingUser->isDriver()) {
                return new JsonResponse(['message' => 'Vous êtes le conducteur de ce covoiturage, vous ne pouvez pas y participer en tant que passager.'], Response::HTTP_BAD_REQUEST);
            }
        }

        // Check if the user is already a passenger
        $isAlreadyPassenger = false;
        foreach ($carpooling->getCarpoolingUsers() as $carpoolingUser) {
            if ($carpoolingUser->getUser() === $user && !$carpoolingUser->isDriver()) {
                $isAlreadyPassenger = true;
                break;
            }
        }

        if ($isAlreadyPassenger) {
            return new JsonResponse(['message' => 'Vous êtes déjà inscrit à ce covoiturage.'], Response::HTTP_CONFLICT);
        }

        // Check available seats
        $currentPassengers = 0;
        foreach ($carpooling->getCarpoolingUsers() as $carpoolingUser) {
            if (!$carpoolingUser->isDriver() && !$carpoolingUser->isCancelled()) {
                $currentPassengers++;
            }
        }

        if ($currentPassengers >= $carpooling->getSeatCount()) {
            return new JsonResponse(['message' => 'Ce covoiturage est complet.'], Response::HTTP_BAD_REQUEST);
        }

        $pricePerPerson = $carpooling->getPricePerPerson();
        $platformFee = 2; // Fixed platform fee

        // Check user credits (must cover the full price)
        if ($user->getCredits() < $pricePerPerson) {
            return new JsonResponse(['message' => 'Crédits insuffisants pour participer à ce covoiturage.'], Response::HTTP_PAYMENT_REQUIRED);
        }

        // Deduct credits from passenger
        $user->setCredits($user->getCredits() - $pricePerPerson);
        $this->manager->persist($user);

        // Get the platform user
        /** @var User|null $platformUser */
        $platformUser = $this->userRepository->find($this->platformUserId);
        if (!$platformUser) {
            // This should ideally not happen if setup correctly, but good to check
            return new JsonResponse(['message' => 'Erreur interne: Utilisateur plateforme non trouvé.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Credit platform fee to platform user
        $platformUser->setCredits($platformUser->getCredits() + $platformFee);
        $this->manager->persist($platformUser);

        // Credit remaining amount to the driver
        /** @var User|null $driver */
        $driver = null;
        foreach ($carpooling->getCarpoolingUsers() as $carpoolingUser) {
            if ($carpoolingUser->isDriver()) {
                $driver = $carpoolingUser->getUser();
                break;
            }
        }

        if (!$driver) {
            return new JsonResponse(['message' => 'Erreur interne: Conducteur non trouvé pour ce covoiturage.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $driverReceiveAmount = $pricePerPerson - $platformFee;
        if ($driverReceiveAmount < 0) {
            $driverReceiveAmount = 0; // Ensure driver doesn't get negative credits if fee is higher than price
        }

        $driver->setCredits($driver->getCredits() + $driverReceiveAmount);
        $this->manager->persist($driver);

        // Add user as a passenger
        $carpoolingUser = new CarpoolingUser();
        $carpoolingUser->setUser($user);
        $carpoolingUser->setCarpooling($carpooling);
        $carpoolingUser->setIsDriver(false);
        $carpoolingUser->setIsCancelled(false);

        $this->manager->persist($carpoolingUser);

        $this->manager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Participation enregistrée avec succès.',
            'newCredits' => $user->getCredits() // Return passenger's new credits
        ], Response::HTTP_OK);
    }


    #[Route('/{id}', name: 'delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $carpooling = $this->repository->find($id);

        if (!$carpooling) {
            return new JsonResponse(['message' => 'Covoiturage non trouvé.'], Response::HTTP_NOT_FOUND);
        }

        $user = $this->security->getUser();
        if (!$user) {
            return new JsonResponse(['message' => 'Authentification requise.'], Response::HTTP_UNAUTHORIZED);
        }

        $isDriver = false;
        $carpoolingUserToDelete = null;
        foreach ($carpooling->getCarpoolingUsers() as $carpoolingUser) {
            if ($carpoolingUser->getUser() === $user && $carpoolingUser->isDriver()) {
                $isDriver = true;
                $carpoolingUserToDelete = $carpoolingUser;
                break;
            }
        }

        if (!$isDriver) {
            return new JsonResponse(['message' => 'Vous n\'êtes pas autorisé à supprimer ce covoiturage. Seul le conducteur peut le faire.'], Response::HTTP_FORBIDDEN);
        }

        if ($carpoolingUserToDelete) {
            $this->manager->remove($carpoolingUserToDelete);
        }

        $this->manager->remove($carpooling);
        $this->manager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
