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
use DateTime; // Ajout pour les dates/heures

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
        $carpoolings = $this->repository->findAll(); // Récupère tous les covoiturages

        return $this->json($carpoolings, Response::HTTP_OK, [], [
            'groups' => 'carpooling:read',
            // 'enable_max_depth' => true // Supprimé car les groupes sont plus précis et suffisent
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

        // NOUVELLE LOGIQUE : Définit isEco en fonction de l'énergie de la voiture
        if ($car->getEnergy() === 'Électrique') {
            $carpooling->setIsEco(true);
        } else {
            // Si l'énergie n'est pas électrique, utilise la valeur fournie ou false par défaut
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

        // Logique pour isEco dans la méthode edit
        // Récupère la voiture associée au covoiturage
        $car = $carpooling->getCar();
        if ($car && $car->getEnergy() === 'Électrique') {
            $carpooling->setIsEco(true);
        } else {
            // Si l'énergie n'est pas électrique, utilise la valeur fournie dans la requête ou la valeur existante
            $carpooling->setIsEco($data['isEco'] ?? $carpooling->getIsEco());
        }

        $this->manager->flush();

        $responseData = $this->serializer->normalize($carpooling, 'json', [
            'groups' => 'carpooling:read',
        ]);

        return new JsonResponse($responseData, status: Response::HTTP_OK);
    }
}
