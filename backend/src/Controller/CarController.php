<?php

namespace App\Controller;

use App\Entity\Car;
use App\Repository\CarRepository;
use App\Repository\CarpoolingRepository; // ⭐ NOUVEL IMPORT : Pour vérifier les covoiturages liés
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException; // ⭐ NOUVEL IMPORT : Pour capturer l'erreur spécifique
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
        private CarpoolingRepository $carpoolingRepository, // ⭐ INJECTION : Ajoute le CarpoolingRepository
    ) {}

    /**
     * Retrieves all vehicles for the authenticated user.
     */
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

        // Vérifie si des covoiturages sont liés à cette voiture
        $associatedCarpoolingsCount = $this->carpoolingRepository->count(['car' => $car]);

        if ($associatedCarpoolingsCount > 0) {
            return new JsonResponse(
                ['message' => 'Ce véhicule ne peut pas être supprimé car il est utilisé dans un ou plusieurs covoiturages.'],
                Response::HTTP_CONFLICT // Code 409 Conflict
            );
        }

        // Si aucun covoiturage n'est lié, procéder à la suppression
        try {
            $this->manager->remove($car);
            $this->manager->flush();

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            // Pour toute autre erreur inattendue lors de la suppression
            return new JsonResponse(['message' => 'Une erreur inattendue est survenue lors de la suppression du véhicule.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
