<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use OpenApi\Attributes as OA;
use App\Service\TokenService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Psr\Log\LoggerInterface;

#[Route('/api', name: 'app_api_')]
final class SecurityController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private SerializerInterface $serializer,
        private TokenService $tokenService,
        private UserRepository $userRepository,
        private LoggerInterface $logger
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
                        new OA\Property(property: "id", type: "integer", example: 1),
                        new OA\Property(property: "email", type: "string", example: "adresse@email.com"),
                        new OA\Property(property: "user", type: "string", example: "Nom d'utilisateur"),
                        new OA\Property(property: "firstName", type: "string", example: "User fisrt name"),
                        new OA\Property(property: "lastName", type: "string", example: "User last name"),
                        new OA\Property(property: "apiToken", type: "string", example: "31a023e212f116124a36af14ea0c1c3806eb9378"),
                        new OA\Property(property: "credits", type: "integer", example: 20),
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
                        new OA\Property(property: "id", type: "integer", example: 1),
                        new OA\Property(property: "user", type: "string", example: "Nom d'utilisateur"),
                        new OA\Property(property: "email", type: "string", example: "adresse@email.com"),
                        new OA\Property(property: "apiToken", type: "string", example: "31a023e212f116124a36af14ea0c1c3806eb9378"),
                        new OA\Property(property: "credits", type: "integer", example: 50),
                        new OA\Property(property: "roles", type: "array", items: new OA\Items(type: "string", example: "ROLE_USER")),
                    ]
                )
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

        // Force user entity refresh to ensure latest data, especially credits
        $this->manager->refresh($user);

        return new JsonResponse([
            'id' => $user->getId(),
            'user' => $user->getUserIdentifier(),
            'email' => $user->getEmail(),
            'apiToken' => $user->getApiToken(),
            'credits' => $user->getCredits(), // Ajouté ici
            'roles' => $user->getRoles(),
        ], Response::HTTP_OK);
    }

    // Account/me route / API Doc
    // Cette route sert de "check-auth" car elle est protégée par IsGranted et retourne les infos de l'utilisateur
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
                new OA\Property(property: "createdAt", type: "string", format: "date-time"),
                new OA\Property(property: "updatedAt", type: "string", format: "date-time"),
                new OA\Property(property: "apiToken", type: "string", example: "abcdef1234567890"),
                new OA\Property(property: "credits", type: "integer", example: 50), // Ajouté ici
                new OA\Property(property: "hasAvatar", type: "boolean", example: true),
                new OA\Property(property: "isDriver", type: "boolean", example: false),
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: "Unauthorized"
    )]

    // Me function (serves as check-auth)
    #[IsGranted('ROLE_USER')]
    public function me(#[CurrentUser] ?User $user): JsonResponse
    {
        if (null === $user) {
            // Cette condition est techniquement redondante avec #[IsGranted('ROLE_USER')]
            // car si l'utilisateur n'est pas trouvé par #[CurrentUser], IsGranted aurait déjà renvoyé 401.
            // Cependant, la laisser ne fait pas de mal pour la clarté.
            return new JsonResponse(['message' => 'Missing credentials or user not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        // Force user entity refresh to ensure latest data (très important pour les crédits !)
        $this->manager->refresh($user);

        // Ensure 'user:read' group is defined on properties to expose in User entity
        // Tu devras t'assurer que 'credits' est exposé dans ce groupe de sérialisation si tu utilises des groupes.
        $userData = json_decode(
            $this->serializer->serialize(
                $user,
                'json',
                ['groups' => ['user:read']]
            ),
            true
        );

        // Ajout explicite des crédits si non inclus par les groupes de sérialisation
        if (!isset($userData['credits'])) {
            $userData['credits'] = $user->getCredits();
        }

        $userData['hasAvatar'] = $user->getPhoto() !== null;
        $userData['isDriver'] = $user->isDriver();

        return new JsonResponse(
            $userData,
            Response::HTTP_OK
        );
    }

    // NEW ROUTE: Upload avatar (BLOB)
    #[Route('/account/me/avatar', name: 'upload_avatar', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Post(
        path: "/api/account/me/avatar",
        summary: "Upload or update user avatar",
        requestBody: new OA\RequestBody(
            required: true,
            description: "Avatar image file",
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    type: "object",
                    properties: [
                        new OA\Property(property: "avatar", type: "string", format: "binary", description: "The image file to upload")
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Avatar uploaded successfully",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Profile picture updated successfully."),
                        new OA\Property(property: "hasAvatar", type: "boolean", example: true)
                    ]
                )
            ),
            new OA\Response(response: 400, description: "Bad request (e.g., no file, invalid type)"),
            new OA\Response(response: 401, description: "Unauthorized"),
            new OA\Response(response: 413, description: "Payload too large (file too big)")
        ]
    )]
    public function uploadAvatar(Request $request, #[CurrentUser] ?User $user): JsonResponse
    {
        $this->logger->info('Starting uploadAvatar method.');

        if (null === $user) {
            $this->logger->error('uploadAvatar: User not authenticated.');
            return new JsonResponse(['message' => 'User not authenticated'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        /** @var UploadedFile $avatarFile */
        $avatarFile = $request->files->get('avatar');
        $this->logger->info('uploadAvatar: Avatar file retrieved.', ['filename' => $avatarFile ? $avatarFile->getClientOriginalName() : 'N/A']);

        if (!$avatarFile) {
            $this->logger->error('uploadAvatar: No avatar file provided.');
            return new JsonResponse(['message' => 'No avatar file provided.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // --- 1. File Validation ---
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $mimeType = $avatarFile->getMimeType();
        $this->logger->info('uploadAvatar: File MIME type.', ['mimeType' => $mimeType]);

        if (!in_array($mimeType, $allowedMimeTypes)) {
            $this->logger->error('uploadAvatar: Unauthorized file type.', ['mimeType' => $mimeType]);
            return new JsonResponse(['message' => 'Unauthorized file type. Only images (JPEG, PNG, GIF, WebP) are accepted.'], JsonResponse::HTTP_UNSUPPORTED_MEDIA_TYPE);
        }

        $maxFileSize = 2 * 1024 * 1024; // 2 MB
        $fileSize = $avatarFile->getSize();
        $this->logger->info('uploadAvatar: File size.', ['fileSize' => $fileSize]);

        if ($fileSize > $maxFileSize) {
            $this->logger->error('uploadAvatar: File is too large.', ['fileSize' => $fileSize, 'maxSize' => $maxFileSize]);
            return new JsonResponse(['message' => 'The file is too large (max 2MB).'], JsonResponse::HTTP_REQUEST_ENTITY_TOO_LARGE);
        }

        try {
            // --- 2. Read binary file content ---
            $binaryContent = file_get_contents($avatarFile->getPathname());
            $this->logger->info('uploadAvatar: Binary file content successfully read.');

            // --- 3. Store BLOB and MIME type in user entity ---
            $user->setPhoto($binaryContent);
            $user->setPhotoMimeType($mimeType);

            $this->manager->flush(); // Persist changes to the database
            $this->logger->info('uploadAvatar: Changes flushed to database.');

            return new JsonResponse([
                'message' => 'Profile picture updated successfully.',
                'hasAvatar' => true
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            $this->logger->error('uploadAvatar: Unexpected error during avatar processing.', [
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return new JsonResponse(['message' => 'An internal error occurred while processing your photo.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // NEW ROUTE: Retrieve avatar (BLOB)
    #[Route('/account/me/avatar-blob', name: 'get_avatar_blob', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Get(
        path: "/api/account/me/avatar-blob",
        summary: "Get user avatar as a binary blob",
        responses: [
            new OA\Response(
                response: 200,
                description: "Avatar image content",
                content: new OA\MediaType(
                    mediaType: "image/*",
                    schema: new OA\Schema(type: "string", format: "binary")
                )
            )
        ]
    )]
    public function getAvatarBlob(#[CurrentUser] ?User $user): Response
    {
        $this->logger->info('Starting getAvatarBlob method.');

        if (null === $user) {
            $this->logger->error('getAvatarBlob: User not authenticated.');
            return new JsonResponse(['message' => 'User not authenticated'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        // Force user entity refresh to ensure latest data
        $this->manager->refresh($user);

        $photoContent = $user->getPhoto();
        $mimeType = $user->getPhotoMimeType();
        $this->logger->info('getAvatarBlob: Photo content and MIME type retrieved from user.', ['hasPhoto' => $photoContent !== null, 'mimeType' => $mimeType]);

        if (!$photoContent) {
            $this->logger->error('getAvatarBlob: Avatar not found for this user.');
            return new JsonResponse(['message' => 'Avatar not found for this user.'], JsonResponse::HTTP_NOT_FOUND);
        }

        // If the BLOB is a stream resource, it must be read
        if (is_resource($photoContent)) {
            $photoContent = stream_get_contents($photoContent);
            $this->logger->info('getAvatarBlob: Photo content read from stream resource.');
        }

        // Create a response with binary content and correct Content-Type
        $response = new Response($photoContent);
        $response->headers->set('Content-Type', $mimeType ?: 'application/octet-stream');
        // Headers to prevent aggressive browser caching
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        $this->logger->info('getAvatarBlob: Avatar response prepared and sent.');

        return $response;
    }

    // Account/edit route / API Doc
    #[Route('/account/edit', name: 'edit', methods: ['PUT'])]
    #[IsGranted('ROLE_USER')]
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
                new OA\Property(property: "createdAt", type: "string", format: "date-time"),
                new OA\Property(property: "updatedAt", type: "string", format: "date-time"),
                new OA\Property(property: "apiToken", type: "string", example: "abcdef1234567890"),
                new OA\Property(property: "credits", type: "integer", example: 50), // Ajouté ici
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: "Unauthorized"
    )]

    // Edit function
    #[IsGranted('ROLE_USER')]
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

        // Ajout explicite des crédits si non inclus par les groupes de sérialisation
        $serializedUserData = json_decode(
            $this->serializer->serialize(
                $user,
                'json',
                ['groups' => ['user:read']]
            ),
            true
        );
        if (!isset($serializedUserData['credits'])) {
            $serializedUserData['credits'] = $user->getCredits();
        }

        return new JsonResponse(
            $serializedUserData,
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

    /**
     * Updates the driver status of the authenticated user.
     */
    #[Route('/account/me/driver-status', name: 'update_driver_status', methods: ['PATCH'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Patch(
        path: "/api/account/me/driver-status",
        summary: "Update the driver status of the current user",
        requestBody: new OA\RequestBody(
            required: true,
            description: "New driver status (boolean)",
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(property: "isDriver", type: "boolean", example: true)
                ],
                required: ["isDriver"]
            )
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Driver status updated successfully",
        content: new OA\JsonContent(
            type: "object",
            properties: [
                new OA\Property(property: "message", type: "string", example: "Driver mode activated."),
                new OA\Property(property: "isDriver", type: "boolean", example: true)
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: "Bad Request (e.g., missing or invalid 'isDriver' value)"
    )]
    #[OA\Response(
        response: 401,
        description: "Unauthorized"
    )]
    public function updateDriverStatus(Request $request, #[CurrentUser] ?User $user): JsonResponse
    {
        if (null === $user) {
            $this->logger->error('updateDriverStatus: User not authenticated.');
            return new JsonResponse(['message' => 'Authentication required'], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);
        $isDriver = $data['isDriver'] ?? null;

        if (!isset($data['isDriver']) || !is_bool($isDriver)) {
            $this->logger->error('updateDriverStatus: "isDriver" value is missing or not a boolean.', ['data' => $data]);
            return new JsonResponse(['error' => '"isDriver" value must be a boolean.'], Response::HTTP_BAD_REQUEST);
        }

        $user->setDriver($isDriver);
        $this->manager->flush();

        $message = $isDriver ? 'Driver mode activated.' : 'Driver mode deactivated.';
        $this->logger->info('updateDriverStatus: Driver status updated.', ['userId' => $user->getId(), 'isDriver' => $isDriver]);

        return new JsonResponse(['message' => $message, 'isDriver' => $user->isDriver()], Response::HTTP_OK);
    }
}
