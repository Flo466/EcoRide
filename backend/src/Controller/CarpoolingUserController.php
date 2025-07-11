<?php

namespace App\Controller;

use App\Entity\CarpoolingUser;
use App\Repository\CarpoolingRepository;
use App\Repository\CarpoolingUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;


#[Route('api/carpooling_user', name: 'app_api_carpooling_user_')]
final class CarpoolingUserController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private CarpoolingRepository $carpoolingRepository,
        private CarpoolingUserRepository $carpoolingUserRepository,
        private SerializerInterface $serializer
    ) {}

    #[Route('/', name: 'add_carpooling_user', methods: ['POST'])]
    public function addCarpoolingUser(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $carpooling = $this->carpoolingRepository->find($data['carpooling_id'] ?? null);
        if (!$carpooling) {
            return new JsonResponse(['error' => 'Carpooling not found'], Response::HTTP_NOT_FOUND);
        }

        $carpoolingUser = new CarpoolingUser();
        $carpoolingUser->setUser($user);
        $carpoolingUser->setCarpooling($carpooling);
        $carpoolingUser->setIsDriver($data['is_driver'] ?? false);

        $this->manager->persist($carpoolingUser);
        $this->manager->flush();

        // Serialize CarpoolingUser with 'carpooling_user:read' group
        $responseData = $this->serializer->normalize($carpoolingUser, 'json', ['groups' => 'carpooling_user:read']);

        return new JsonResponse($responseData, Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'show_carpooling_user', methods: ['GET'])]
    public function showCarpoolingUser(int $id): JsonResponse
    {
        $carpoolingUser = $this->carpoolingUserRepository->find($id);

        if (!$carpoolingUser) {
            return new JsonResponse(['error' => 'CarpoolingUser not found'], Response::HTTP_NOT_FOUND);
        }

        // Serialize CarpoolingUser with 'carpooling_user:read' group
        $responseData = $this->serializer->normalize($carpoolingUser, 'json', ['groups' => 'carpooling_user:read']);

        return new JsonResponse($responseData, Response::HTTP_OK);
    }

    #[Route('/', name: 'list_carpooling_users', methods: ['GET'])]
    public function listCarpoolingUsers(): JsonResponse
    {
        $carpoolingUsers = $this->carpoolingUserRepository->findAll();

        // Serialize CarpoolingUsers with 'carpooling_user:read' group
        $responseData = $this->serializer->normalize($carpoolingUsers, 'json', ['groups' => 'carpooling_user:read']);

        return new JsonResponse($responseData, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'update_carpooling_user', methods: ['PUT'])]
    public function updateCarpoolingUser(int $id, Request $request): JsonResponse
    {
        $carpoolingUser = $this->carpoolingUserRepository->find($id);

        if (!$carpoolingUser) {
            return new JsonResponse(['error' => 'CarpoolingUser not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        $this->serializer->deserialize(
            $request->getContent(),
            CarpoolingUser::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $carpoolingUser, AbstractNormalizer::IGNORED_ATTRIBUTES => ['user', 'carpooling']]
        );

        if (isset($data['is_driver'])) {
            $carpoolingUser->setIsDriver((bool) $data['is_driver']);
        }

        $this->manager->flush();

        // Serialize CarpoolingUser with 'carpooling_user:read' group
        $responseData = $this->serializer->normalize($carpoolingUser, 'json', ['groups' => 'carpooling_user:read']);

        return new JsonResponse($responseData, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete_carpooling_user', methods: ['DELETE'])]
    public function deleteCarpoolingUser(int $id): JsonResponse
    {
        $carpoolingUser = $this->carpoolingUserRepository->find($id);

        if (!$carpoolingUser) {
            return new JsonResponse(['error' => 'CarpoolingUser not found'], Response::HTTP_NOT_FOUND);
        }

        $this->manager->remove($carpoolingUser);
        $this->manager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
