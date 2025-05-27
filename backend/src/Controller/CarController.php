<?php

namespace App\Controller;

use App\Entity\Car;
use App\Repository\CarRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('api/car', name: 'app_api_car_')]
final class CarController extends AbstractController
{
    public function __construct(private EntityManagerInterface $manager, private CarRepository $repository)
    {
    }

    #[Route(name: 'new', methods: 'POST')]
    public function new(): Response
    {
        $car = new Car();

        //Implémenter logique(formulaire)

        $this->manager->persist($car);
        $this->manager->flush();

        return $this->json(
            ['message' => "Car resource created with {$car->getId()} id"],
            status: Response::HTTP_CREATED
        );
    }

    #[Route('/{id}', '/show', name: 'show', methods: 'GET')]
    public function show(int $id): Response
    {
        $car = $this->repository->findOneBy(['id' => $id]);

        if (!$car) {
            throw $this->createNotFoundException("No car found for {$id} id");
        }

        return $this->json(['message' => "A car was found : {$car->getId()}"]);
    }

    #[Route('/{id}', name: 'edit', methods: 'PUT')]
    public function edit(int $id): Response
    {
        $car = $this->repository->findOneBy(['id' => $id]);

        if (!$car) {
            throw $this->createNotFoundException("No car found for {$id} id");
        }

        //Iplémenter logique puis flush

        $this->manager->flush();
        return $this->redirectToRoute('app_api_car_show', ['id' => $car->getId()]);
    }

    #[Route('/{id}', name: 'delete', methods: 'DELETE')]
    public function delete(int $id): Response
    {
        $car = $this->repository->findOneBy(['id' => $id]);

        if (!$car) {
            throw $this->createNotFoundException("No car found for {$id} id");
        }

        $this->manager->remove($car);
        $this->manager->flush();
        return $this->json(['message' => 'car resource deleted'], status: Response::HTTP_NO_CONTENT);
    }
}
