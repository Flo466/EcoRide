<?php

namespace App\Controller;

use App\Entity\Car;
use App\Repository\CarRepository;
use App\Repository\CarpoolingRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Symfony\Component\Security\Core\Security;
use App\Repository\BrandRepository;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('api/car', name: 'app_api_car_')]
final class CarController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private CarRepository $repository,
        private BrandRepository $brandRepository,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator,
        private Security $security,
        private CarpoolingRepository $carpoolingRepository,
    ) {}

    // =========================================================================
    // I. Car Listing Routes
    // =========================================================================

    /**
     *
     * ////////////////////////////////////////////////////////////////////////
     * /// ROUTE: List All Cars for User
     * /// FUNCTION: Retrieves all vehicles owned by the authenticated user.
     * ////////////////////////////////////////////////////////////////////////
     *
     */
    #[Route('/all-cars', name: 'list_all_cars', methods: ['GET'])]
    public function listAllCars(CarRepository $carRepository, SerializerInterface $serializer): JsonResponse
    {
        $user = $this->security->getUser();

        if (!$user) {
            return new JsonResponse(['message' => 'Authentication required.'], Response::HTTP_UNAUTHORIZED);
        }

        $cars = $carRepository->findByUserWithBrand($user);
        $jsonCars = $serializer->serialize($cars, 'json', ['groups' => ['car:read', 'brand:read']]);

        return new JsonResponse($jsonCars, Response::HTTP_OK, [], true);
    }

    // =========================================================================
    // II. Car Management Routes
    // =========================================================================

    /**
     *
     * ////////////////////////////////////////////////////////////////////////
     * /// ROUTE: Create New Car
     * /// FUNCTION: Creates a new car entry for the authenticated user.
     * ////////////////////////////////////////////////////////////////////////
     *
     */
    #[Route('/', name: 'new', methods: ['POST'])]
    public function new(Request $request): JsonResponse
    {
        $user = $this->security->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'Authentication required'], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);

        $car = $this->serializer->deserialize(
            $request->getContent(),
            Car::class,
            'json'
        );

        $car->setCreatedAt(new DateTimeImmutable());

        $brandId = $data['brand_id'] ?? null;
        if ($brandId) {
            $brand = $this->brandRepository->find($brandId);
            if ($brand) {
                $car->setBrand($brand);
            } else {
                return new JsonResponse(['message' => 'Car brand not found.'], JsonResponse::HTTP_BAD_REQUEST);
            }
        } else {
            return new JsonResponse(['message' => 'Brand ID is required.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        if (isset($data['firstRegistrationDate']) && is_string($data['firstRegistrationDate'])) {
            $date = DateTimeImmutable::createFromFormat('d/m/Y', $data['firstRegistrationDate']);
            if ($date === false) {
                return new JsonResponse([
                    'message' => 'Invalid first registration date format. Expected DD/MM/YYYY.'
                ], JsonResponse::HTTP_BAD_REQUEST);
            }
            $car->setFirstRegistrationDate($date);
        }

        $car->setUser($user);

        $this->manager->persist($car);
        $this->manager->flush();

        $responseData = $this->serializer->serialize($car, 'json', ['groups' => ['car:read', 'brand:read']]);
        $location = $this->urlGenerator->generate(
            'app_api_car_show',
            ['id' => $car->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return new JsonResponse($responseData, Response::HTTP_CREATED, ['Location' => $location], true);
    }

    /**
     *
     * ////////////////////////////////////////////////////////////////////////
     * /// ROUTE: Show Car Details
     * /// FUNCTION: Retrieves a single car by its ID, ensuring user ownership.
     * ////////////////////////////////////////////////////////////////////////
     *
     */
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id, CarRepository $carRepository): JsonResponse
    {
        $car = $carRepository->find($id);

        if (!$car) {
            return new JsonResponse(['message' => 'Vehicle not found'], Response::HTTP_NOT_FOUND);
        }

        $user = $this->security->getUser();
        if (!$user || $car->getUser() !== $user) {
            throw $this->createAccessDeniedException('Access denied to this vehicle.');
        }

        $responseData = $this->serializer->serialize($car, 'json', ['groups' => ['car:read', 'brand:read']]);

        return new JsonResponse($responseData, Response::HTTP_OK, [], true);
    }

    /**
     *
     * ////////////////////////////////////////////////////////////////////////
     * /// ROUTE: Edit Car
     * /// FUNCTION: Updates an existing car, ensuring user ownership.
     * ////////////////////////////////////////////////////////////////////////
     *
     */
    #[Route('/{id}', name: 'edit', methods: ['PUT', 'PATCH'])]
    public function edit(int $id, Request $request, CarRepository $carRepository): JsonResponse
    {
        $car = $carRepository->find($id);

        if (!$car) {
            return new JsonResponse(['message' => 'Vehicle not found'], Response::HTTP_NOT_FOUND);
        }

        $user = $this->security->getUser();
        if (!$user || $car->getUser() !== $user) {
            throw $this->createAccessDeniedException('Access denied to this vehicle.');
        }

        $this->serializer->deserialize(
            $request->getContent(),
            Car::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $car]
        );

        $car->setUpdatedAt(new DateTimeImmutable());
        $this->manager->flush();

        $responseData = $this->serializer->serialize($car, 'json', ['groups' => ['car:read', 'brand:read']]);
        $location = $this->urlGenerator->generate(
            'app_api_car_show',
            ['id' => $car->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return new JsonResponse($responseData, Response::HTTP_OK, ['Location' => $location], true);
    }

    /**
     *
     * ////////////////////////////////////////////////////////////////////////
     * /// ROUTE: Delete Car
     * /// FUNCTION: Deletes a car, ensuring user ownership and no linked carpoolings.
     * ////////////////////////////////////////////////////////////////////////
     *
     */
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id, CarRepository $carRepository): Response
    {
        $car = $carRepository->find($id);

        if (!$car) {
            return new JsonResponse(['message' => 'Vehicle not found'], Response::HTTP_NOT_FOUND);
        }

        $user = $this->security->getUser();
        if (!$user || $car->getUser() !== $user) {
            throw $this->createAccessDeniedException('Access denied to this vehicle.');
        }

        // Check if any carpoolings are linked to this car
        $associatedCarpoolingsCount = $this->carpoolingRepository->count(['car' => $car]);

        if ($associatedCarpoolingsCount > 0) {
            return new JsonResponse(
                ['message' => 'This vehicle cannot be deleted as it is used in one or more carpoolings.'],
                Response::HTTP_CONFLICT // 409 Conflict
            );
        }

        // If no carpoolings are linked, proceed with deletion
        try {
            $this->manager->remove($car);
            $this->manager->flush();

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            // Catch any unexpected errors during deletion
            return new JsonResponse(['message' => 'An unexpected error occurred while deleting the vehicle.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
