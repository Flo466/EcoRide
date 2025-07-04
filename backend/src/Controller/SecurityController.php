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
                new OA\Property(property: "hasAvatar", type: "boolean", example: true),
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: "Unauthorized"
    )]

    // Me function
    #[IsGranted('ROLE_USER')]
    public function me(#[CurrentUser] ?User $user): JsonResponse
    {
        if (null === $user) {
            return new JsonResponse(['message' => 'Missing credentials'], Response::HTTP_UNAUTHORIZED);
        }

        // NOUVEAU : Force le rafraîchissement de l'entité utilisateur pour s'assurer d'avoir les dernières données
        $this->manager->refresh($user);

        $userData = json_decode(
            $this->serializer->serialize(
                $user,
                'json',
                ['groups' => ['user_read']]
            ),
            true
        );

        $userData['hasAvatar'] = $user->getPhoto() !== null;

        return new JsonResponse(
            $userData,
            Response::HTTP_OK
        );
    }

    // NOUVELLE ROUTE : Upload de l'avatar (BLOB)
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
                        new OA\Property(property: "message", type: "string", example: "Photo de profil mise à jour avec succès."),
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
        $this->logger->info('Début de la méthode uploadAvatar.');

        if (null === $user) {
            $this->logger->error('uploadAvatar: Utilisateur non authentifié.');
            return new JsonResponse(['message' => 'Utilisateur non authentifié'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        /** @var UploadedFile $avatarFile */
        $avatarFile = $request->files->get('avatar');
        $this->logger->info('uploadAvatar: Fichier avatar récupéré.', ['filename' => $avatarFile ? $avatarFile->getClientOriginalName() : 'N/A']);

        if (!$avatarFile) {
            $this->logger->error('uploadAvatar: Aucun fichier d\'avatar fourni.');
            return new JsonResponse(['message' => 'Aucun fichier d\'avatar fourni.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // --- 1. Validation du fichier ---
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $mimeType = $avatarFile->getMimeType();
        $this->logger->info('uploadAvatar: Type MIME du fichier.', ['mimeType' => $mimeType]);

        if (!in_array($mimeType, $allowedMimeTypes)) {
            $this->logger->error('uploadAvatar: Type de fichier non autorisé.', ['mimeType' => $mimeType]);
            return new JsonResponse(['message' => 'Type de fichier non autorisé. Seules les images (JPEG, PNG, GIF, WebP) sont acceptées.'], JsonResponse::HTTP_UNSUPPORTED_MEDIA_TYPE);
        }

        $maxFileSize = 2 * 1024 * 1024; // 2 MB
        $fileSize = $avatarFile->getSize();
        $this->logger->info('uploadAvatar: Taille du fichier.', ['fileSize' => $fileSize]);

        if ($fileSize > $maxFileSize) {
            $this->logger->error('uploadAvatar: Le fichier est trop grand.', ['fileSize' => $fileSize, 'maxSize' => $maxFileSize]);
            return new JsonResponse(['message' => 'Le fichier est trop grand (max 2MB).'], JsonResponse::HTTP_REQUEST_ENTITY_TOO_LARGE);
        }

        try {
            // --- 2. Lecture du contenu binaire du fichier ---
            $binaryContent = file_get_contents($avatarFile->getPathname());
            $this->logger->info('uploadAvatar: Contenu binaire du fichier lu avec succès.');

            // --- 3. Stockage du BLOB et du type MIME dans l'entité utilisateur ---
            $user->setPhoto($binaryContent);
            $user->setPhotoMimeType($mimeType);

            $this->manager->flush(); // Persiste les changements en base de données
            $this->logger->info('uploadAvatar: Changements flushés en base de données.');

            return new JsonResponse([
                'message' => 'Photo de profil mise à jour avec succès.',
                'hasAvatar' => true
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            $this->logger->error('uploadAvatar: Erreur inattendue lors du traitement de l\'avatar.', [
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return new JsonResponse(['message' => 'Une erreur interne est survenue lors du traitement de votre photo.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // NOUVELLE ROUTE : Récupération de l'avatar (BLOB)
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
        $this->logger->info('Début de la méthode getAvatarBlob.');

        if (null === $user) {
            $this->logger->error('getAvatarBlob: Utilisateur non authentifié.');
            return new JsonResponse(['message' => 'Utilisateur non authentifié'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        // NOUVEAU : Force le rafraîchissement de l'entité utilisateur pour s'assurer d'avoir les dernières données
        $this->manager->refresh($user);

        $photoContent = $user->getPhoto();
        $mimeType = $user->getPhotoMimeType();
        $this->logger->info('getAvatarBlob: Contenu photo et MIME type récupérés de l\'utilisateur.', ['hasPhoto' => $photoContent !== null, 'mimeType' => $mimeType]);

        if (!$photoContent) {
            $this->logger->error('getAvatarBlob: Avatar non trouvé pour cet utilisateur.');
            return new JsonResponse(['message' => 'Avatar non trouvé pour cet utilisateur.'], JsonResponse::HTTP_NOT_FOUND);
        }

        // Si le BLOB est une ressource stream, il faut la lire
        if (is_resource($photoContent)) {
            $photoContent = stream_get_contents($photoContent);
            $this->logger->info('getAvatarBlob: Contenu photo lu depuis la ressource stream.');
        }

        // Créer une réponse avec le contenu binaire et le bon Content-Type
        $response = new Response($photoContent);
        $response->headers->set('Content-Type', $mimeType ?: 'application/octet-stream');
        // NOUVEAU : En-têtes pour empêcher la mise en cache agressive par le navigateur
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        $this->logger->info('getAvatarBlob: Réponse d\'avatar préparée et envoyée.');

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
