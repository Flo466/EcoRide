<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use OpenApi\Attributes as OA;
use App\Service\TokenService;  // Importation du service TokenService
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

#[Route('/api', name: 'app_api_')]
final class SecurityController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private SerializerInterface $serializer,
        private TokenService $tokenService,
        private UserRepository $userRepository
    ) {}

    // Registration Route / API doc
    #[Route('/registration', name: 'registration', methods: ['POST'])]
    #[OA\Post(
        path: "/api/registration",
        summary: "Registering a new user",
        requestBody: new OA\RequestBody(
            required: true,
            description: "User data required for registration",
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(property: "firstName", type: "string", example: "Fisrt name"),
                    new OA\Property(property: "lastName", type: "string", example: "Last name"),
                    new OA\Property(property: "userName", type: "string", example: "User name"),
                    new OA\Property(property: "email", type: "string", example: "adresse@email.com"),
                    new OA\Property(property: "password", type: "string", example: "password")
                ],
                required: ["userName", "email", "password", "firstName", "lastName"],
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "User successfully registered",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "user", type: "string", example: "Nom d'utilisateur"),
                        new OA\Property(property: "firstName", type: "string", example: "User fisrt name"),
                        new OA\Property(property: "lastName", type: "string", example: "User last name"),
                        new OA\Property(property: "apiToken", type: "string", example: "31a023e212f116124a36af14ea0c1c3806eb9378"),
                        new OA\Property(property: "roles", type: "array", items: new OA\Items(type: "string", example: "ROLE_USER")),
                    ]
                )
            )
        ]
    )]

    // Registration function
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $user = $this->serializer->deserialize(
            $request->getContent(),
            User::class,
            format: 'json'
        );

        $user->setPassword($passwordHasher->hashPassword($user, $user->getPassword()));

        $user->setCreatedAt(new \DateTimeImmutable());

        $this->tokenService->setApiToken($user);
        $user->setCredits(20);

        $this->manager->persist($user);
        $this->manager->flush();

        return new JsonResponse([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'user' => $user->getUserIdentifier(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'apiToken' => $user->getApiToken(),
            'credits' => $user->getCredits(),
            'roles' => $user->getRoles()
        ], status: Response::HTTP_CREATED);
    }

    // Login route / API Doc
    #[Route('/login', name: 'login', methods: ['POST'])]
    #[OA\Post(
        path: "/api/login",
        summary: "Logging in a user",
        requestBody: new OA\RequestBody(
            required: true,
            description: "User credentials for login",
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(property: "email", type: "string", example: "adresse@email.com"),
                    new OA\Property(property: "password", type: "string", example: "Mot de passe")
                ],
                required: ["email", "password"]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "User successfully logged in",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "user", type: "string", example: "Nom d'utilisateur"),
                        new OA\Property(property: "apiToken", type: "string", example: "31a023e212f116124a36af14ea0c1c3806eb9378"),
                        new OA\Property(property: "roles", type: "array", items: new OA\Items(type: "string", example: "ROLE_USER")),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Invalid credentials"
            ),
            new OA\Response(
                response: 400,
                description: "Missing email or password"
            )
        ]
    )]

    //Login function
    public function login(Request $request, UserPasswordHasherInterface $hasher): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        if (!$email || !$password) {
            return new JsonResponse(['message' => 'Email and password required'], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->manager->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user || !$hasher->isPasswordValid($user, $password)) {
            return new JsonResponse(['message' => 'Incorrect credentials'], Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse([
            'id' => $user->getId(),
            'user' => $user->getUserIdentifier(),
            'email' => $user->getEmail(),
            'apiToken' => $user->getApiToken(),
            'roles' => $user->getRoles(),
        ], Response::HTTP_OK);
    }

    // Account/me route / API Doc
    #[Route('/account/me', name: 'me', methods: ['GET'])]
    #[OA\Get(
        path: "/api/account/me",
        summary: "Get user data"
    )]
    #[OA\Response(
        response: 200,
        description: "Data successfully found",
        content: new OA\JsonContent(
            type: "object",
            properties: [
                new OA\Property(property: "id", type: "integer", example: 1),
                new OA\Property(property: "firstName", type: "string", example: "first name"),
                new OA\Property(property: "lastName", type: "string", example: "last name"),
                new OA\Property(property: "email", type: "string", example: "user@example.com"),
                new OA\Property(property: "created_at", type: "string", format: "date-time"),
                new OA\Property(property: "updated_at", type: "string", format: "date-time"),
                new OA\Property(property: "api_token", type: "string", example: "abcdef1234567890"),
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: "Unauthorized"
    )]

    // Me function
    public function me(#[CurrentUser] ?User $user): JsonResponse
    {
        if (null === $user) {
            return new JsonResponse(['message' => 'Missing credentials'], Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse(
            json_decode(
                $this->serializer->serialize(
                    $user,
                    'json',
                    ['groups' => ['user_read']]
                ),
                true
            ),
            Response::HTTP_OK
        );
    }

    // Account/edit route / API Doc
    #[Route('/account/edit', name: 'edit', methods: ['PUT'])]
    #[OA\Put(
        path: "/api/account/edit",
        summary: "Edit a user",
        requestBody: new OA\RequestBody(
            required: true,
            description: "User data required for edition",
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(property: "firstName", type: "string", example: "Nouveau prénom")
                ],
            )
        )
    )]
    #[OA\Response(
        response: 200,
        description: "User updated successfully",
        content: new OA\JsonContent(
            type: "object",
            properties: [
                new OA\Property(property: "id", type: "integer", example: 1),
                new OA\Property(property: "email", type: "string", example: "user@example.com"),
                new OA\Property(property: "firstName", type: "string", example: "first name"),
                new OA\Property(property: "lastName", type: "string", example: "last name"),
                new OA\Property(property: "created_at", type: "string", format: "date-time"),
                new OA\Property(property: "updated_at", type: "string", format: "date-time"),
                new OA\Property(property: "api_token", type: "string", example: "abcdef1234567890"),
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: "Unauthorized"
    )]

    // Edit function
    public function edit(#[CurrentUser] ?User $user, Request $request): JsonResponse
    {
        if (null === $user) {
            return new JsonResponse(['message' => 'Missing credentials'], Response::HTTP_UNAUTHORIZED);
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
            json_decode(
                $this->serializer->serialize(
                    $user,
                    'json',
                    ['groups' => ['user_read']]
                ),
                true
            ),
            Response::HTTP_OK
        );
    }

    #[Route('/check-userName', name: 'app_check_userName', methods: ['POST'])]
    public function checkUserName(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $userName = $data['userName'] ?? null;

        if (null === $userName) {
            return new JsonResponse(['message' => 'Missing user name'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $existingUser = $this->userRepository->findOneBy(['userName' => $userName]);

        if ($existingUser) {
            return new JsonResponse(['isAvailable' => false]);
        }
        return new JsonResponse(['isAvailable' => true]);
    }

    #[Route('/check-email', name: 'app_check_email', methods: ['POST'])]
    public function checkEmail(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;

        if (null === $email) {
            return new JsonResponse(['message' => 'Missing email.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $existingUser = $this->userRepository->findOneBy(['email' => $email]);

        if ($existingUser) {
            return new JsonResponse(['isAvailable' => false]);
        }
        return new JsonResponse(['isAvailable' => true]);
    }
}
