<?php

namespace App\Controller;

use App\Entity\Car;
use App\Repository\CarRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
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
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator
        )
    {
    }

    #[Route(name: 'new', methods: 'POST')]
    public function new(Request $request, ): JsonResponse
    {
        $car = $this->serializer->deserialize(
            $request->getContent(),
            type: Car::class,
            format: 'json');
            $car->setCreatedAt(new DateTimeImmutable());

        //Implémenter logique(formulaire)

        $this->manager->persist($car);
        $this->manager->flush();
        
        $responseData = $this->serializer->serialize($car, format: 'json');
        $location = $this->urlGenerator->generate(
            name: 'app_api_car_show',
            parameters: ['id' => $car->getId()],
            referenceType: UrlGeneratorInterface::ABSOLUTE_URL
        );

        return new JsonResponse(
            data: $responseData,
            status: Response::HTTP_CREATED,
            headers: ["Location" => $location],
            json: true
        );
    }
    // Show function
    #[Route('/{id}', name: 'show', methods: 'GET')]
    public function show(int $id): JsonResponse
    {
        $car = $this->repository->findOneBy(['id' => $id]);

        if ($car) {
            $respondeData = $this->serializer->serialize($car, format:'json');

            return new JsonResponse($respondeData, status: Response::HTTP_OK);
        }

         return new JsonResponse(data: null, status: Response::HTTP_NOT_FOUND);
    }

    // Edit function
    #[Route('/{id}', name: 'edit', methods: 'PUT')]
    public function edit(int $id, Request $request): JsonResponse
    {
        $car = $this->repository->findOneBy(['id' => $id]);

        if ($car) {
            $car = $this->serializer->deserialize(
                $request->getContent(),
                Car::class,
                'json',
                [AbstractNormalizer::OBJECT_TO_POPULATE => $car]
            );
        }

        //Iplémenter logique puis flush

        $car->setUpdatedAt(new DateTimeImmutable());
        $this->manager->flush();
        
        $responseData = $this->serializer->serialize($car, format:'json');
            $location = $this->urlGenerator->generate(
                name: 'app_api_car_show',
                parameters: ['id' => $car->getId()],
                referenceType: UrlGeneratorInterface::ABSOLUTE_URL
            );
            
            return new JsonResponse(
                data: $responseData,
                status: Response::HTTP_OK,
                headers: ["Location" => $location],
                json: true
            );
    }

    #[Route('/{id}', name: 'delete', methods: 'DELETE')]
    public function delete(int $id): JsonResponse
    {
        $car = $this->repository->findOneBy(['id' => $id]);

        if ($car) {
            $this->manager->remove($car);
            $this->manager->flush();

            return new JsonResponse(null, status: Response::HTTP_NO_CONTENT);
        }

        return new JsonResponse(null, status: Response::HTTP_NOT_FOUND);
    }
}
