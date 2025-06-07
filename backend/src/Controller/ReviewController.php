<?php

// src/Controller/ReviewController.php

namespace App\Controller;

use App\Entity\Review;
use App\Enum\ReviewStatus;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Core\Security;
use DateTimeImmutable;

#[Route('/api/review', name: 'app_api_review_')]
final class ReviewController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private ReviewRepository $repository,
        private SerializerInterface $serializer,
        private Security $security
    ) {}

    #[Route('', name: 'new', methods: ['POST'])]
    public function new(Request $request): JsonResponse
    {
        $user = $this->security->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'Authentication required'], Response::HTTP_UNAUTHORIZED);
        }

        /** @var Review $review */
        $review = $this->serializer->deserialize($request->getContent(), Review::class, 'json');

        $review->setUser($user);
        $review->setStatus(ReviewStatus::PENDING);
        $review->setCreatedAt(new DateTimeImmutable());

        $this->em->persist($review);
        $this->em->flush();

       $responseData = $this->serializer->serialize($review, 'json', ['groups' => ['review:read']]);


        return new JsonResponse($responseData, Response::HTTP_CREATED, [], true);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $review = $this->repository->find($id);

        if (!$review) {
            return new JsonResponse(['message' => 'Review not found'], Response::HTTP_NOT_FOUND);
        }

        $responseData = $this->serializer->serialize($review, 'json', ['groups' => ['review:read']]);

        return new JsonResponse($responseData, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}/status', name: 'update_status', methods: ['PATCH'])]
    public function updateStatus(int $id, Request $request): JsonResponse
    {
        $review = $this->repository->find($id);

        if (!$review) {
            return new JsonResponse(['message' => 'Review not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $statusValue = $data['status'] ?? null;

        if (!$statusValue) {
            return new JsonResponse(['message' => 'Status is required'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $statusEnum = ReviewStatus::from($statusValue);
        } catch (\ValueError $e) {
            return new JsonResponse(['message' => 'Invalid status'], Response::HTTP_BAD_REQUEST);
        }

        $review->setStatus($statusEnum);
        $this->em->flush();

        return new JsonResponse(['status' => $review->getStatus()->value], Response::HTTP_OK);
    }
}
