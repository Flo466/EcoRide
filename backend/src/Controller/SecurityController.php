<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
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

    // =========================================================================
    // I. Authentication Routes
    // =========================================================================

    /**
     *
     * ////////////////////////////////////////////////////////////////////////
     * /// ROUTE: User Registration
     * /// FUNCTION: Registers a new user with provided credentials.
     * ////////////////////////////////////////////////////////////////////////
     *
     */
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

    /**
     *
     * ////////////////////////////////////////////////////////////////////////
     * /// ROUTE: User Login
     * /// FUNCTION: Authenticates a user and provides their data including API token.
     * ////////////////////////////////////////////////////////////////////////
     *
     */
    #[Route('/login', name: 'login', methods: ['POST'])]
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
            'credits' => $user->getCredits(),
            'roles' => $user->getRoles(),
        ], Response::HTTP_OK);
    }

    // =========================================================================
    // II. User Account Routes
    // =========================================================================

    /**
     *
     * ////////////////////////////////////////////////////////////////////////
     * /// ROUTE: Get User Profile
     * /// FUNCTION: Retrieves the authenticated user's profile information.
     * ///             Serves as an authentication check.
     * ////////////////////////////////////////////////////////////////////////
     *
     */
    #[Route('/account/me', name: 'me', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function me(#[CurrentUser] ?User $user): JsonResponse
    {
        if (null === $user) {
            return new JsonResponse(['message' => 'Missing credentials or user not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $this->manager->refresh($user);

        $userData = json_decode(
            $this->serializer->serialize(
                $user,
                'json',
                ['groups' => ['user:read']]
            ),
            true
        );

        // Explicitly add credits if not included by serialization groups
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

    /**
     *
     * ////////////////////////////////////////////////////////////////////////
     * /// ROUTE: Upload User Avatar
     * /// FUNCTION: Uploads or updates the authenticated user's avatar (profile picture).
     * ////////////////////////////////////////////////////////////////////////
     *
     */
    #[Route('/account/me/avatar', name: 'upload_avatar', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
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

    /**
     *
     * ////////////////////////////////////////////////////////////////////////
     * /// ROUTE: Retrieve User Avatar (BLOB)
     * /// FUNCTION: Retrieves the authenticated user's avatar as a binary stream.
     * ////////////////////////////////////////////////////////////////////////
     *
     */
    #[Route('/account/me/avatar-blob', name: 'get_avatar_blob', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
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

    /**
     *
     * ////////////////////////////////////////////////////////////////////////
     * /// ROUTE: Edit User Account
     * /// FUNCTION: Allows an authenticated user to update their account details.
     * ////////////////////////////////////////////////////////////////////////
     *
     */
    #[Route('/account/edit', name: 'edit', methods: ['PUT'])]
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

        // Explicitly add credits if not included by serialization groups
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

    // =========================================================================
    // III. User Data Validation Routes
    // =========================================================================

    /**
     *
     * ////////////////////////////////////////////////////////////////////////
     * /// ROUTE: Check Username Availability
     * /// FUNCTION: Checks if a given username is already in use.
     * ////////////////////////////////////////////////////////////////////////
     *
     */
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

    /**
     *
     * ////////////////////////////////////////////////////////////////////////
     * /// ROUTE: Check Email Availability
     * /// FUNCTION: Checks if a given email is already registered.
     * ////////////////////////////////////////////////////////////////////////
     *
     */
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

    // =========================================================================
    // IV. Driver Status Routes
    // =========================================================================

    /**
     *
     * ////////////////////////////////////////////////////////////////////////
     * /// ROUTE: Update Driver Status
     * /// FUNCTION: Updates the driver status (isDriver) of the authenticated user.
     * ////////////////////////////////////////////////////////////////////////
     *
     */
    #[Route('/account/me/driver-status', name: 'update_driver_status', methods: ['PATCH'])]
    #[IsGranted('ROLE_USER')]
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
