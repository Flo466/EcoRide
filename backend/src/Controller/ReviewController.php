<?php

namespace App\Controller;

use App\Entity\Review;
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

#[Route('api/review', name: 'app_api_review_')]
final class ReviewController extends AbstractController
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
        $review = $this->serializer->deserialize(
            $request->getContent(),
            type: Review::class,
            format: 'json');
            $review->setCreatedAt(new DateTimeImmutable());

        //Implémenter logique(formulaire)

        $review->setUser($review);

        $this->manager->persist($review);
        $this->manager->flush();
        
        $responseData = $this->serializer->serialize($review, format: 'json');
        $location = $this->urlGenerator->generate(
            name: 'app_api_review_show',
            parameters: ['id' => $review->getId()],
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
        $review = $this->repository->findOneBy(['id' => $id]);

        if ($review) {
            $respondeData = $this->serializer->serialize($review, format:'json');

            return new JsonResponse($respondeData, status: Response::HTTP_OK);
        }

         return new JsonResponse(data: null, status: Response::HTTP_NOT_FOUND);
    }

    // Edit function
    #[Route('/{id}', name: 'edit', methods: 'PUT')]
    public function edit(int $id, Request $request): JsonResponse
    {
        $review = $this->repository->findOneBy(['id' => $id]);

        if ($review) {
            $review = $this->serializer->deserialize(
                $request->getContent(),
                Review::class,
                'json',
                [AbstractNormalizer::OBJECT_TO_POPULATE => $review]
            );
        }

        //Iplémenter logique puis flush

        $review->setUpdatedAt(new DateTimeImmutable());
        $this->manager->flush();
        
        $responseData = $this->serializer->serialize($review, format:'json');
            $location = $this->urlGenerator->generate(
                name: 'app_api_review_show',
                parameters: ['id' => $review->getId()],
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
        $review = $this->repository->findOneBy(['id' => $id]);

        if ($review) {
            $this->manager->remove($review);
            $this->manager->flush();

            return new JsonResponse(null, status: Response::HTTP_NO_CONTENT);
        }

        return new JsonResponse(null, status: Response::HTTP_NOT_FOUND);
    }
}
