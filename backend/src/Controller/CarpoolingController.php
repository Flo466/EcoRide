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
use DateTime;

#[Route('api/carpoolings', name: 'app_api_carpooling_')]
final class CarpoolingController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private CarpoolingRepository $repository,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator,
        private NormalizerInterface $normalizer,
        private Security $security
    ) {
        $this->repository = $repository;
        $this->serializer = $serializer;
        $this->security = $security;
        $this->normalizer = $normalizer;
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function listAllCarpoolings(): JsonResponse
    {
        $carpoolings = $this->repository->findAll();

        return $this->json($carpoolings, Response::HTTP_OK, [], [
            'groups' => 'carpooling:read',
        ]);
    }

    /**
     * Lists all carpoolings for the authenticated user (as driver or passenger).
     */
    #[Route('/list-by-user', name: 'list_by_user', methods: ['GET'])]
    public function listCarpoolingsByUser(): JsonResponse
    {
        // Get the currently authenticated user
        $user = $this->security->getUser();

        // If no user is authenticated, return an unauthorized response
        if (!$user) {
            return new JsonResponse(['message' => 'Authentification requise pour accéder à vos covoiturages.'], Response::HTTP_UNAUTHORIZED);
        }

        $carpoolings = $this->repository->findByUser($user);

        // Serialize the carpooling data
        return $this->json($carpoolings, Response::HTTP_OK, [], [
            'groups' => [
                'carpooling:read',
                'car:read',      // Include car details
                'brand:read',    // Include car brand details
                'user:read'      // Include user details (for driver/passengers)
            ],
        ]);
    }


    #[Route('', name: 'new', methods: 'POST')]
    public function new(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $carpooling = new Carpooling();

        // Departure Date
        if (isset($data['departureDate'])) {
            $departureDate = DateTime::createFromFormat('Y-m-d', $data['departureDate']);
            if ($departureDate === false) {
                return new JsonResponse(['message' => 'Invalid departureDate format. Expected YYYY-MM-DD.'], Response::HTTP_BAD_REQUEST);
            }
            $carpooling->setDepartureDate($departureDate);
        } else {
            return new JsonResponse(['message' => 'departureDate is required.'], Response::HTTP_BAD_REQUEST);
        }

        // Departure Time
        if (isset($data['departureTime'])) {
            $departureTime = DateTime::createFromFormat('H:i:s', $data['departureTime']);
            if ($departureTime === false) {
                return new JsonResponse(['message' => 'Invalid departureTime format. Expected HH:MM:SS.'], Response::HTTP_BAD_REQUEST);
            }
            $carpooling->setDepartureTime($departureTime);
        } else {
            return new JsonResponse(['message' => 'departureTime is required.'], Response::HTTP_BAD_REQUEST);
        }

        // Arrival Date
        if (isset($data['arrivalDate'])) {
            $arrivalDate = DateTime::createFromFormat('Y-m-d', $data['arrivalDate']);
            if ($arrivalDate === false) {
                return new JsonResponse(['message' => 'Invalid arrivalDate format. Expected YYYY-MM-DD.'], Response::HTTP_BAD_REQUEST);
            }
            $carpooling->setArrivalDate($arrivalDate);
        } else {
            return new JsonResponse(['message' => 'arrivalDate is required.'], Response::HTTP_BAD_REQUEST);
        }

        // Arrival Time
        if (isset($data['arrivalTime'])) {
            $arrivalTime = DateTime::createFromFormat('H:i:s', $data['arrivalTime']);
            if ($arrivalTime === false) {
                return new JsonResponse(['message' => 'Invalid arrivalTime format. Expected HH:MM:SS.'], Response::HTTP_BAD_REQUEST);
            }
            $carpooling->setArrivalTime($arrivalTime);
        } else {
            return new JsonResponse(['message' => 'arrivalTime is required.'], Response::HTTP_BAD_REQUEST);
        }

        $car = $this->manager->getRepository(Car::class)->find($data['car']);
        if (!$car) {
            return new JsonResponse(['message' => 'Car not found'], Response::HTTP_BAD_REQUEST);
        }

        $carpooling->setCar($car);
        $carpooling->setCreatedAt(new DateTimeImmutable());
        $carpooling->setStatus(CarpoolingStatus::OPEN);

        // Autres champs
        $carpooling->setDeparturePlace($data['departurePlace'] ?? null);
        $carpooling->setArrivalPlace($data['arrivalPlace'] ?? null);
        $carpooling->setSeatCount($data['availableSeats'] ?? null);
        $carpooling->setPricePerPerson($data['pricePerPassenger'] ?? null);
        // $carpooling->setIsEco($data['isEco'] ?? false); // Ligne d'origine

        if ($car->getEnergy() === 'Électrique') {
            $carpooling->setIsEco(true);
        } else {
            $carpooling->setIsEco($data['isEco'] ?? false);
        }

        $carpooling->setStatus(CarpoolingStatus::tryFrom($data['status'] ?? CarpoolingStatus::OPEN->value));

        // Utilisateur authentifié
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

        $responseData = $this->serializer->normalize($carpooling, 'json', [
            'groups' => 'carpooling:read',
        ]);

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
                    'carpooling:read',
                    'car:read',
                    'brand:read',
                    'user:read'
                ],
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

        return $this->json($results, 200, [], [
            'groups' => 'carpooling:read',
        ]);
    }

    #[Route('/{id}', name: 'edit', requirements: ['id' => '\d+'], methods: ['PUT'])]
    public function edit(int $id, Request $request): JsonResponse
    {
        $carpooling = $this->repository->find($id);

        if (!$carpooling) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['departureDate']) && isset($data['departureTime'])) {
            $departureDateTime = DateTime::createFromFormat('Y-m-d H:i:s', $data['departureDate'] . ' ' . $data['departureTime']);
            if ($departureDateTime !== false) {
                $carpooling->setDepartureDate($departureDateTime);
                $carpooling->setDepartureTime($departureDateTime);
            }
        }
        if (isset($data['arrivalDate']) && isset($data['arrivalTime'])) {
            $arrivalDateTime = DateTime::createFromFormat('Y-m-d H:i:s', $data['arrivalDate'] . ' ' . $data['arrivalTime']);
            if ($arrivalDateTime !== false) {
                $carpooling->setArrivalDate($arrivalDateTime);
                $carpooling->setArrivalTime($arrivalDateTime);
            }
        }

        $carpooling->setDeparturePlace($data['departurePlace'] ?? $carpooling->getDeparturePlace());
        $carpooling->setArrivalPlace($data['arrivalPlace'] ?? $carpooling->getArrivalPlace());
        $carpooling->setPricePerPerson($data['pricePerPassenger'] ?? $carpooling->getPricePerPerson());

        $car = $carpooling->getCar();
        if ($car && $car->getEnergy() === 'Électrique') {
            $carpooling->setIsEco(true);
        } else {
            $carpooling->setIsEco($data['isEco'] ?? $carpooling->getIsEco());
        }

        $this->manager->flush();

        $responseData = $this->serializer->normalize($carpooling, 'json', [
            'groups' => 'carpooling:read',
        ]);

        return new JsonResponse($responseData, status: Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $carpooling = $this->repository->find($id);

        // 1. Vérifier si le covoiturage existe
        if (!$carpooling) {
            return new JsonResponse(['message' => 'Covoiturage non trouvé.'], Response::HTTP_NOT_FOUND);
        }

        // 2. Vérifier l'utilisateur authentifié
        $user = $this->security->getUser();
        if (!$user) {
            return new JsonResponse(['message' => 'Authentification requise.'], Response::HTTP_UNAUTHORIZED);
        }

        // 3. Vérifier si l'utilisateur authentifié est le conducteur du covoiturage
        $isDriver = false;
        $carpoolingUserToDelete = null; // Pour stocker l'objet CarpoolingUser du conducteur
        foreach ($carpooling->getCarpoolingUsers() as $carpoolingUser) {
            if ($carpoolingUser->getUser() === $user && $carpoolingUser->getIsDriver()) {
                $isDriver = true;
                $carpoolingUserToDelete = $carpoolingUser;
                break;
            }
        }

        if (!$isDriver) {
            return new JsonResponse(['message' => 'Vous n\'êtes pas autorisé à supprimer ce covoiturage. Seul le conducteur peut le faire.'], Response::HTTP_FORBIDDEN);
        }

        // 4. Supprimer l'entrée CarpoolingUser du conducteur (si trouvée)
        if ($carpoolingUserToDelete) {
            $this->manager->remove($carpoolingUserToDelete);
        }

        // 5. Supprimer le covoiturage
        $this->manager->remove($carpooling);
        $this->manager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT); // 204 No Content pour une suppression réussie
    }
}
