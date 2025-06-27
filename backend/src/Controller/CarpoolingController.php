<?php

namespace App\Controller;

use App\Entity\Car;
use App\Entity\Carpooling;
use App\Entity\CarpoolingUser;
use App\Enum\CarpoolingStatus;
use OpenApi\Attributes as OA;
use App\Repository\CarpoolingRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;


#[Route('api/carpooling', name: 'app_api_carpooling_')]
final class CarpoolingController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private CarpoolingRepository $repository,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator,
        private NormalizerInterface $normalizer,
        private Security $security
        )
    {
        $this->repository = $repository;
        $this->serializer = $serializer;
        $this->security = $security;
        $this->normalizer = $normalizer;
    }

    #[Route('/', name: 'new', methods: 'POST')]
    public function new(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $car = $this->manager->getRepository(Car::class)->find($data['car']);

        if (!$car) {
            return new JsonResponse(['message' => 'Car not found'], Response::HTTP_BAD_REQUEST);
        }

        $carpooling = $this->serializer->deserialize(
            $request->getContent(),
            Carpooling::class,
            'json',
            [AbstractNormalizer::IGNORED_ATTRIBUTES => ['car']]
        );
        $carpooling->setCar($car);
        $carpooling->setCreatedAt(new DateTimeImmutable());
        $carpooling->setStatus(CarpoolingStatus::OPEN);

        $user = $this->security->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'User not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $carpoolingUser = new CarpoolingUser();
        $carpoolingUser->setUser($user);
        $carpoolingUser->setCarpooling($carpooling);
        $carpoolingUser->setIsDriver(true);

        $this->manager->persist($carpooling);
        $this->manager->persist($carpoolingUser);
        $this->manager->flush();
        
        $responseData = $this->serializer->normalize($carpooling, 'json', ['groups' => 'carpooling_read']);
        $location = $this->urlGenerator->generate(
            name: 'app_api_carpooling_show',
            parameters: ['id' => $carpooling->getId()],
            referenceType: UrlGeneratorInterface::ABSOLUTE_URL
        );

        return new JsonResponse(
            data: $responseData,
            status: Response::HTTP_CREATED,
            headers: ["Location" => $location]
        );
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $carpooling = $this->repository->find($id);

        if ($carpooling) {
            $responseData = $this->serializer->normalize($carpooling, 'json', [
                'groups' => [
                    'carpooling_read',
                    'car_read',
                    'brand_read',
                    'user_read'
                ]
            ]);
            return new JsonResponse($responseData, status: Response::HTTP_OK);
        }

        return new JsonResponse(data: null, status: Response::HTTP_NOT_FOUND);
    }

   #[Route('/search', name: 'app_carpooling_search', methods: ['GET'])]
    public function search(Request $request, CarpoolingRepository $repository): JsonResponse
    {
        $departurePlace = $request->query->get('departurePlace');
        $arrivalPlace = $request->query->get('arrivalPlace');
        $departureDateString = $request->query->get('departureDate');
        $departureDate = null;

        if ($departureDateString) {
            try {
                $departureDate = new \DateTimeImmutable($departureDateString);
            } catch (\Exception $e) {
                return new JsonResponse(['error' => 'Invalid departureDate format'], 400);
            }
        }

        $results = $repository->findBySearchCriteria($departurePlace, $arrivalPlace, $departureDate);

        return $this->json($results, 200, [], ['groups' => 'carpooling_read']);
    }


    #[Route('/{id}', name: 'edit', requirements: ['id' => '\d+'], methods: ['PUT'])]
    public function edit(int $id, Request $request): JsonResponse
    {
        $carpooling = $this->repository->find($id);

        if (!$carpooling) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        $this->serializer->deserialize(
            $request->getContent(),
            Carpooling::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $carpooling]
        );

        $carpooling->setUpdatedAt(new \DateTimeImmutable());

        $this->manager->flush();

        $responseData = $this->serializer->normalize($carpooling, 'json', ['groups' => 'carpooling_read']);
        $location = $this->urlGenerator->generate(
            'app_api_carpooling_show',
            ['id' => $carpooling->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return new JsonResponse(
            data: $responseData,
            status: Response::HTTP_OK,
            headers: ['Location' => $location]
        );
    }

   #[Route('/{id}', name: 'delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $carpooling = $this->repository->findOneBy(['id' => $id]);

        if ($carpooling) {
            $this->manager->remove($carpooling);
            $this->manager->flush();

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }
}