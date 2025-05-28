<?php

namespace App\Controller;

use App\Entity\Review;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('api/review', name: 'app_api_review_')]
final class ReviewController extends AbstractController
{
    public function __construct(private EntityManagerInterface $manager, private ReviewRepository $repository)
    {
    }

    #[Route(name: 'new', methods: 'POST')]
    public function new(): Response
    {
        $review = new Review();

        //Implémenter logique(formulaire)

        $this->manager->persist($review);
        $this->manager->flush();

        return $this->json(
            ['message' => "review resource created with {$review->getId()} id"],
            status: Response::HTTP_CREATED
        );
    }

    #[Route('/{id}', name: 'show', methods: 'GET')]
    public function show(int $id): Response
    {
        $review = $this->repository->findOneBy(['id' => $id]);

        if (!$review) {
            throw $this->createNotFoundException("No review found for {$id} id");
        }

        return $this->json(['message' => "A review was found : {$review->getId()}"]);
    }

    #[Route('/{id}', name: 'edit', mnstallethods: 'PUT')]
    public function edit(int $id): Response
    {
        $review = $this->repository->findOneBy(['id' => $id]);

        if (!$review) {
            throw $this->createNotFoundException("No review found for {$id} id");
        }

        //Iplémenter logique puis flush

        $this->manager->flush();
        return $this->redirectToRoute('app_api_review_show', ['id' => $review->getId()]);
    }

    #[Route('/{id}', name: 'delete', methods: 'DELETE')]
    public function delete(int $id): Response
    {
        $review = $this->repository->findOneBy(['id' => $id]);

        if (!$review) {
            throw $this->createNotFoundException("No review found for {$id} id");
        }

        $this->manager->remove($review);
        $this->manager->flush();
        return $this->json(['message' => 'review resource deleted'], status: Response::HTTP_NO_CONTENT);
    }
}
