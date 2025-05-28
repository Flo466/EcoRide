<?php

namespace App\Controller;

use App\Entity\Carpooling;
use App\Repository\CarpoolingRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Util\Json;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route as AnnotationRoute;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('api/carpooling', name: 'app_api_carpooling_')]
final class CarpoolingController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private CarpoolingRepository $repository,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator)
    {
    }
    #[Route(name: 'new', methods: 'POST')]
    public function new(Request $request): JsonResponse
    {
        $carpooling = $this->serializer->deserialize(
            $request->getContent(),
            type: Carpooling::class,
            format: 'json'
        );
        $carpooling->setCreatedAt(new DateTimeImmutable());
        
        //Implémenter logique(formulaire)

        $this->manager->persist($carpooling);
        $this->manager->flush();
        
        $responseData = $this->serializer->serialize($carpooling, format: 'json');
        $location = $this->urlGenerator->generate(
            name: 'app_api_carpooling_show',
            parameters: ['id' => $carpooling->getId()],
            referenceType: UrlGeneratorInterface::ABSOLUTE_URL
        );

        return new JsonResponse(
            data: $responseData,
            status: Response::HTTP_CREATED,
            headers: ["Location" => $location],
            json: true
        );
    }

    #[Route('/{id}', name: 'show', methods: 'GET')]
    public function show(int $id): JsonResponse
    {
        $carpooling = $this->repository->findOneBy(['id' => $id]);

        if ($carpooling) {
            $respondeData = $this->serializer->serialize($carpooling, format:'json');

            return new JsonResponse($respondeData, status: Response::HTTP_OK);
        }

         return new JsonResponse(data: null, status: Response::HTTP_NOT_FOUND);
    }

    #[Route('/{id}', name: 'edit', methods: 'PUT')]
    public function edit(int $id, Request $request): JsonResponse
    {
        $carpooling = $this->repository->findOneBy(['id' => $id]);

        if ($carpooling) {
            $carpooling = $this->serializer->deserialize(
                $request->getContent(),
                Carpooling::class,
                'json',
                [AbstractNormalizer::OBJECT_TO_POPULATE => $carpooling]
            );
        }

        //Iplémenter logique puis flush

        $carpooling->setUpdatedAt(new DateTimeImmutable());
        $this->manager->flush();
        
        $responseData = $this->serializer->serialize($carpooling, format:'json');
            $location = $this->urlGenerator->generate(
                name: 'app_api_carpooling_show',
                parameters: ['id' => $carpooling->getId()],
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
        $carpooling = $this->repository->findOneBy(['id' => $id]);

        if ($carpooling) {
            $this->manager->remove($carpooling);
            $this->manager->flush();

            return new JsonResponse(null, status: Response::HTTP_NO_CONTENT);
        }

        return new JsonResponse(null, status: Response::HTTP_NOT_FOUND);
    }
}
