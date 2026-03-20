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

    #[Route('/registration', name: 'registration', methods: ['POST'])]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $user = $this->serializer->deserialize($request->getContent(), User::class, 'json');
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
        ], Response::HTTP_CREATED);
    }

    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(Request $request, UserPasswordHasherInterface $hasher): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        if (!$email || !$password) {
            return new JsonResponse(['message' => 'Email and password required'], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user || !$hasher->isPasswordValid($user, $password)) {
            return new JsonResponse(['message' => 'Incorrect credentials'], Response::HTTP_UNAUTHORIZED);
        }

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

    #[Route('/account/me', name: 'me', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function me(#[CurrentUser] ?User $user): JsonResponse
    {
        if (null === $user) {
            return new JsonResponse(['message' => 'User not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $this->manager->refresh($user);

        $userData = json_decode(
            $this->serializer->serialize($user, 'json', ['groups' => ['user:read']]),
            true
        );

        $userData['hasAvatar'] = $user->getPhoto() !== null;
        $userData['photoUrl'] = $user->getPhoto() ? '/uploads/avatars/' . $user->getPhoto() : null;

        return new JsonResponse($userData, Response::HTTP_OK);
    }

    /**
     * Upload or Update Avatar (Fix: On force la récupération de l'entité)
     */
    #[Route('/account/me/avatar', name: 'upload_avatar', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function uploadAvatar(Request $request, #[CurrentUser] ?User $user): JsonResponse
    {
        if (!$user) {
            return new JsonResponse(['message' => 'User not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        /** @var UploadedFile $avatarFile */
        $avatarFile = $request->files->get('avatar');

        if (!$avatarFile) {
            return new JsonResponse(['message' => 'No avatar file provided.'], Response::HTTP_BAD_REQUEST);
        }

        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($avatarFile->getMimeType(), $allowedMimeTypes)) {
            return new JsonResponse(['message' => 'Unauthorized file type.'], Response::HTTP_UNSUPPORTED_MEDIA_TYPE);
        }

        try {            
            $newFilename = uniqid().'.'.$avatarFile->guessExtension();
            $destination = $this->getParameter('kernel.project_dir').'/public/uploads/avatars';

            // --- FIX CRITIQUE : Récupération d'une instance "managée" par Doctrine ---
            $userFromDb = $this->userRepository->find($user->getId());

            // 1. Suppression de l'ancienne photo physique
            if ($userFromDb->getPhoto()) {
                $oldFilePath = $destination.'/'.$userFromDb->getPhoto();
                if (file_exists($oldFilePath)) {
                    unlink($oldFilePath);
                }
            }

            // 2. Déplacement du nouveau fichier
            $avatarFile->move($destination, $newFilename);

            $userFromDb = $this->userRepository->find($user->getId());
            $userFromDb->setPhoto($newFilename);
            $userFromDb->setUpdatedAt(new \DateTimeImmutable());

            $this->manager->persist($userFromDb);
            $this->manager->flush();
            
            return new JsonResponse([
                'message' => 'Profile picture updated successfully.',
                'photo' => $newFilename,
                'photoUrl' => '/uploads/avatars/'.$newFilename 
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            $this->logger->error('Avatar Upload Error: ' . $e->getMessage());
            return new JsonResponse(['message' => 'Error during upload: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/account/edit', name: 'edit', methods: ['PUT'])]
    #[IsGranted('ROLE_USER')]
    public function edit(#[CurrentUser] ?User $user, Request $request): JsonResponse
    {
        if (null === $user) {
            return new JsonResponse(['message' => 'Missing credentials'], Response::HTTP_UNAUTHORIZED);
        }

        // Rechargement pour garantir l'update
        $userFromDb = $this->userRepository->find($user->getId());

        $this->serializer->deserialize(
            $request->getContent(),
            User::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $userFromDb]
        );

        $userFromDb->setUpdatedAt(new DateTimeImmutable());
        $this->manager->flush();

        $userData = json_decode(
            $this->serializer->serialize($userFromDb, 'json', ['groups' => ['user:read']]),
            true
        );

        return new JsonResponse($userData, Response::HTTP_OK);
    }

    // =========================================================================
    // III. User Data Validation & Driver Status
    // =========================================================================

    #[Route('/check-userName', name: 'app_check_userName', methods: ['POST'])]
    public function checkUserName(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $userName = $data['userName'] ?? null;
        $existingUser = $this->userRepository->findOneBy(['userName' => $userName]);
        return new JsonResponse(['isAvailable' => !$existingUser]);
    }

    #[Route('/check-email', name: 'app_check_email', methods: ['POST'])]
    public function checkEmail(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;
        $existingUser = $this->userRepository->findOneBy(['email' => $email]);
        return new JsonResponse(['isAvailable' => !$existingUser]);
    }

    #[Route('/account/me/driver-status', name: 'update_driver_status', methods: ['PATCH'])]
    #[IsGranted('ROLE_USER')]
    public function updateDriverStatus(Request $request, #[CurrentUser] ?User $user): JsonResponse
    {
        if (null === $user) {
            return new JsonResponse(['message' => 'Authentication required'], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);
        $isDriver = $data['isDriver'] ?? null;

        if (!isset($data['isDriver']) || !is_bool($isDriver)) {
            return new JsonResponse(['error' => '"isDriver" must be a boolean.'], Response::HTTP_BAD_REQUEST);
        }

        $userFromDb = $this->userRepository->find($user->getId());
        $userFromDb->setDriver($isDriver);
        $userFromDb->setUpdatedAt(new \DateTimeImmutable());
        $this->manager->flush();

        return new JsonResponse([
            'message' => $isDriver ? 'Driver mode activated.' : 'Driver mode deactivated.',
            'isDriver' => $userFromDb->isDriver()
        ], Response::HTTP_OK);
    }
}