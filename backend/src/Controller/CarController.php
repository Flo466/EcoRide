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
    ) {
    }

    #[Route(name: 'new', methods: ['POST'])]
    public function new(Request $request): JsonResponse
    {
        $user = $this->security->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'Authentication required'], Response::HTTP_UNAUTHORIZED);
        }

        $car = $this->serializer->deserialize(
            $request->getContent(),
            Car::class,
            'json'
        );
        $car->setCreatedAt(new \DateTimeImmutable());

        $data = json_decode($request->getContent(), true);
        $brandId = $data['brand_id'] ?? null;

        if ($brandId) {
            $brand = $this->brandRepository->find($brandId);
            if ($brand) {
                $car->setBrand($brand);
            } else {
                return new JsonResponse(['message' => 'Brand not found'], JsonResponse::HTTP_BAD_REQUEST);
            }
        } else {
            return new JsonResponse(['message' => 'Brand ID is required'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Validate the first registration date format
        if (!$car->isValidFirstRegistrationDate()) {
            return new JsonResponse([
                'message' => 'Invalid first registration date format. Expected DD/MM/YYYY.'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $car->setUser($user);

        $this->manager->persist($car);
        $this->manager->flush();

        $responseData = $this->serializer->serialize($car, 'json', ['groups' => ['car:read']]);

        $location = $this->urlGenerator->generate(
            'app_api_car_show',
            ['id' => $car->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return new JsonResponse($responseData, Response::HTTP_CREATED, ['Location' => $location], true);
    }


    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $car = $this->repository->find($id);

        if (!$car) {
            return new JsonResponse(['message' => 'Car not found'], Response::HTTP_NOT_FOUND);
        }

        $responseData = $this->serializer->serialize($car, 'json', ['groups' => ['car:read']]);

        return new JsonResponse($responseData, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'edit', methods: ['PUT'])]
    public function edit(int $id, Request $request): JsonResponse
    {
        $car = $this->repository->find($id);

        if (!$car) {
            return new JsonResponse(['message' => 'Car not found'], Response::HTTP_NOT_FOUND);
        }

        $this->serializer->deserialize(
            $request->getContent(),
            Car::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $car]
        );

        $car->setUpdatedAt(new DateTimeImmutable());
        $this->manager->flush();

        $responseData = $this->serializer->serialize($car, 'json', ['groups' => ['car:read']]);
        $location = $this->urlGenerator->generate(
            'app_api_car_show',
            ['id' => $car->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return new JsonResponse($responseData, Response::HTTP_OK, ['Location' => $location], true);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $car = $this->repository->find($id);

        if (!$car) {
            return new JsonResponse(['message' => 'Car not found'], Response::HTTP_NOT_FOUND);
        }

        $this->manager->remove($car);
        $this->manager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
