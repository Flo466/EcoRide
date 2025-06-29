<?php

namespace App\Controller;

use App\Entity\Review;
use App\Entity\User; // Assure-toi que User est bien importé
use App\Enum\ReviewStatus;
use App\Repository\ReviewRepository;
use App\Repository\UserRepository; // Importe le UserRepository pour trouver l'utilisateur cible
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Core\Security;
use DateTimeImmutable;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/review', name: 'app_api_review_')]
final class ReviewController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private ReviewRepository $repository,
        private SerializerInterface $serializer,
        private Security $security,
        private NormalizerInterface $normalizer,
        private UserRepository $userRepository // Injecte le UserRepository
    ) {}

    #[Route('/', name: 'new', methods: ['POST'])]
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

        $responseData = $this->normalizer->normalize($review, 'json', ['groups' => ['review:read']]);

        return new JsonResponse($responseData, Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $review = $this->repository->find($id);

        if (!$review) {
            return new JsonResponse(['message' => 'Review not found'], Response::HTTP_NOT_FOUND);
        }
        $responseData = $this->normalizer->normalize($review, 'json', ['groups' => ['review:read']]);

        return new JsonResponse($responseData, Response::HTTP_OK);
    }

    #[Route('/user/{userId}', name: 'get_by_user', methods: ['GET'])]
    public function getReviewsByUser(int $userId): JsonResponse
    {
        $targetUser = $this->userRepository->find($userId);

        if (!$targetUser) {
            return new JsonResponse(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $reviews = $this->repository->findBy(['user' => $targetUser, 'status' => ReviewStatus::APPROVED]);
        $responseData = $this->normalizer->normalize($reviews, 'json', ['groups' => ['review:read', 'user:read']]);

        return new JsonResponse($responseData, Response::HTTP_OK);
    }


    #[Route('/{id}', name: 'edit', methods: ['PUT'])]
    public function edit(int $id, Request $request): JsonResponse
    {
        $user = $this->security->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Authentication required'], Response::HTTP_UNAUTHORIZED);
        }

        $review = $this->repository->find($id);

        if (!$review) {
            return new JsonResponse(['message' => 'Review not found'], Response::HTTP_NOT_FOUND);
        }

        $this->serializer->deserialize(
            $request->getContent(),
            Review::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $review]
        );

        if ($review->getUser()->getId() !== $user->getId()) {
            return new JsonResponse(['message' => 'Unauthorized to edit this review'], Response::HTTP_FORBIDDEN);
        }

        $review->setUpdatedAt(new DateTimeImmutable());

        $this->em->flush();

        $responseData = $this->normalizer->normalize($review, 'json', ['groups' => ['review:read']]);

        return new JsonResponse($responseData, Response::HTTP_OK);
    }


    #[Route('/{id}/status', name: 'update_status', methods: ['PATCH'])]
    public function updateStatus(int $id, Request $request): JsonResponse
    {
        $user = $this->security->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Authentication required'], Response::HTTP_UNAUTHORIZED);
        }

        $review = $this->repository->find($id);

        if (!$review) {
            return new JsonResponse(['message' => 'Review not found'], Response::HTTP_NOT_FOUND);
        }

        if ($review->getUser()->getId() !== $user->getId() && !$this->isGranted('ROLE_ADMIN')) {
            return new JsonResponse(['message' => 'Unauthorized to update status of this review'], Response::HTTP_FORBIDDEN);
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
        $review->setUpdatedAt(new DateTimeImmutable());
        $this->em->flush();

        return new JsonResponse(['status' => $review->getStatus()->value], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $user = $this->security->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Authentication required'], Response::HTTP_UNAUTHORIZED);
        }

        $review = $this->repository->find($id);

        if (!$review) {
            return new JsonResponse(['message' => 'Review not found'], Response::HTTP_NOT_FOUND);
        }

        if ($review->getUser()->getId() !== $user->getId() && !$this->isGranted('ROLE_ADMIN')) {
            return new JsonResponse(['message' => 'Unauthorized to delete this review'], Response::HTTP_FORBIDDEN);
        }

        $this->em->remove($review);
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
