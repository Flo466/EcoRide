<?php

namespace App\Controller;

use App\Entity\Car;
use App\Repository\CarRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
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
use Symfony\Component\Validator\Validator\ValidatorInterface; // Ajout pour la validation si nécessaire

#[Route('api/car', name: 'app_api_car_')] // Ce préfixe s'applique aux routes ci-dessous
final class CarController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private CarRepository $repository,
        private BrandRepository $brandRepository,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator,
        private Security $security,
        // private ValidatorInterface $validator, // Décommenter si tu as besoin d'une validation manuelle ici
    ) {}

    /**
     * Récupère la liste des véhicules de l'utilisateur authentifié.
     * L'annotation de route est maintenant définie dans config/routes/api_cars.yaml.
     */
    public function listAllCars(CarRepository $carRepository, SerializerInterface $serializer): JsonResponse // Ajout des dépendances pour la clarté
    {
        $user = $this->security->getUser();

        if (!$user) {
            return new JsonResponse(['message' => 'Authentification requise.'], Response::HTTP_UNAUTHORIZED);
        }

        // Récupère les véhicules associés à l'utilisateur connecté
        $cars = $carRepository->findBy(['user' => $user]); // Utilise le repository injecté

        // Sérialise les données des véhicules en JSON
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
                return new JsonResponse(['message' => 'Marque de voiture introuvable.'], JsonResponse::HTTP_BAD_REQUEST);
            }
        } else {
            return new JsonResponse(['message' => 'L\'ID de la marque est requis.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        if (isset($data['firstRegistrationDate']) && is_string($data['firstRegistrationDate'])) {
            $date = DateTimeImmutable::createFromFormat('d/m/Y', $data['firstRegistrationDate']);
            if ($date === false) {
                return new JsonResponse([
                    'message' => 'Format de date de première immatriculation invalide. Attendu JJ/MM/AAAA.'
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
            return new JsonResponse(['message' => 'Véhicule non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $user = $this->security->getUser();
        if (!$user || $car->getUser() !== $user) {
            throw $this->createAccessDeniedException('Accès refusé à ce véhicule.');
        }

        $responseData = $this->serializer->serialize($car, 'json', ['groups' => ['car:read', 'brand:read']]);

        return new JsonResponse($responseData, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'edit', methods: ['PUT', 'PATCH'])]
    public function edit(int $id, Request $request, CarRepository $carRepository): JsonResponse
    {
        $car = $carRepository->find($id);

        if (!$car) {
            return new JsonResponse(['message' => 'Véhicule non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $user = $this->security->getUser();
        if (!$user || $car->getUser() !== $user) {
            throw $this->createAccessDeniedException('Accès refusé à ce véhicule.');
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
    public function delete(int $id, CarRepository $carRepository): JsonResponse
    {
        $car = $carRepository->find($id);

        if (!$car) {
            return new JsonResponse(['message' => 'Véhicule non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $user = $this->security->getUser();
        if (!$user || $car->getUser() !== $user) {
            throw $this->createAccessDeniedException('Accès refusé à ce véhicule.');
        }

        $this->manager->remove($car);
        $this->manager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
