<?php

namespace App\Controller;

use App\Entity\Carpooling;
use App\Repository\CarpoolingRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route as AnnotationRoute;
use Symfony\Component\Routing\Attribute\Route;

#[Route('api/carpooling', name: 'app_api_carpooling_')]
final class CarpoolingController extends AbstractController
{
    public function __construct(private EntityManagerInterface $manager, private CarpoolingRepository $repository)
    {
    }
    #[Route(name: 'new', methods: 'POST')]
    public function new(): Response
    {
        $carpooling = new Carpooling();

        //Implémenter logique(formulaire)

        $this->manager->persist($carpooling);
        $this->manager->flush();

        return $this->json(
            ['message' => "Carpooling resource created with {$carpooling->getId()} id"],
            status: Response::HTTP_CREATED
        );
    }

    #[Route('/{id}', '/show', name: 'show', methods: 'GET')]
    public function show(int $id): Response
    {
        $carpooling = $this->repository->findOneBy(['id' => $id]);

        if (!$carpooling) {
            throw $this->createNotFoundException("No capooling found for {$id} id");
        }

        return $this->json(['message' => "A carpooling was found : {$carpooling->getId()}"]);
    }

    #[Route('/{id}', name: 'edit', methods: 'PUT')]
    public function edit(int $id): Response
    {
        $carpooling = $this->repository->findOneBy(['id' => $id]);

        if (!$carpooling) {
            throw $this->createNotFoundException("No capooling found for {$id} id");
        }

        //Iplémenter logique puis flush

        $this->manager->flush();
        return $this->redirectToRoute('app_api_carpooling_show', ['id' => $carpooling->getId()]);

    }

    #[Route('/{id}', name: 'delete', methods: 'DELETE')]
    public function delete(int $id): Response
    {
        $carpooling = $this->repository->findOneBy(['id' => $id]);

        if (!$carpooling) {
            throw $this->createNotFoundException("No capooling found for {$id} id");
        }

        $this->manager->remove($carpooling);
        $this->manager->flush();
        return $this->json(['message' => 'Carpooling resource deleted'], status: Response::HTTP_NO_CONTENT);
    }
}
