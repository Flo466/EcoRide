<?php

namespace App\Controller;

use App\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/resgistration', name: 'app_api')]
final class SecurityController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private SerializerInterface $serializer
        )
    {
    }

    #[Route('/registration', name: 'registration', methods: ['POST'])]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $user = $this->serializer->deserialize(
            $request->getContent(),
            User::class,
            format: 'json'
        );
        $user->setPassword($passwordHasher->hashPassword($user, $user->getPassword()));
        $user->setCreatedAt(new \DateTimeImmutable());

        $this->manager->persist($user);
        $this->manager->flush();

        return new JsonResponse([
            'user' => $user->getUserIdentifier(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'apiToken' => $user->getApiToken(),
            'roles' => $user->getRoles()],
        status: Response::HTTP_CREATED);
    }

    public function login(Request $request, UserPasswordHasherInterface $hasher):JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

         if (!$email || !$password) {
            return new JsonResponse(['message' => 'Email and paswword required'], Response::HTTP_BAD_REQUEST);
        }

         $user = $this->manager->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user || !$hasher->isPasswordValid($user, $password)) {
            return new JsonResponse(['message' => 'Incorect ID'], Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse([
            'user' => $user->getUserIdentifier(),
            'apiToken' => $user->getApiToken(),
            'roles' => $user->getRoles(),
        ], Response::HTTP_OK);
    }

    public function account(#[CurrentUser] ?User $user): JsonResponse
    {
        if (null === $user) {
            return new JsonResponse(['message' => 'missing credentials'], Response::HTTP_UNAUTHORIZED);
        }
        
        return new JsonResponse(
            json_decode($this->serializer->serialize($user, 'json'), true),
            Response::HTTP_OK
        );
    }

    public function edit(#[CurrentUser] ?User $user, Request $request): JsonResponse
    {
        if (null === $user) {
            return new JsonResponse(['message' => 'missing credentials'], Response::HTTP_UNAUTHORIZED);
        }

        $user = $this->serializer->deserialize(
            $request->getContent(),
            User::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $user]
        );

        $user->setUpdatedAt(new DateTimeImmutable());
        $this->manager->flush();

        return new JsonResponse(
            json_decode($this->serializer->serialize($user, 'json'), true),
            Response::HTTP_OK
        );
    }
}