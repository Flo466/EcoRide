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

// MODIFIÉ : Le préfixe du contrôleur est maintenant au pluriel pour la cohérence RESTful
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

    // NOUVEAU : Route pour lister tous les covoiturages (GET /api/carpoolings)
    #[Route('', name: 'list', methods: ['GET'])]
    public function listAllCarpoolings(): JsonResponse
    {
        $carpoolings = $this->repository->findAll(); // Récupère tous les covoiturages

        // Sérialise avec le groupe 'carpooling:read'
        return $this->json($carpoolings, Response::HTTP_OK, [], ['groups' => 'carpooling:read']);
    }

    // MODIFIÉ : La route de création est maintenant sur le chemin vide '' pour correspondre au préfixe
    #[Route('', name: 'new', methods: 'POST')]
    public function new(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // --- GESTION DES DATES ET HEURES SÉPARÉES ---
        // Le format attendu du frontend est "YYYY-MM-DD" pour la date et "HH:MM:SS" pour l'heure

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
        // --- FIN GESTION DES DATES ET HEURES SÉPARÉES ---


        $car = $this->manager->getRepository(Car::class)->find($data['car']);

        if (!$car) {
            return new JsonResponse(['message' => 'Car not found'], Response::HTTP_BAD_REQUEST);
        }

        // Suppression de la désérialisation automatique ici car nous la faisons manuellement pour les dates
        // $carpooling = $this->serializer->deserialize(
        //     $request->getContent(),
        //     Carpooling::class,
        //     'json',
        //     [AbstractNormalizer::IGNORED_ATTRIBUTES => ['car']]
        // );
        $carpooling->setCar($car);
        $carpooling->setCreatedAt(new DateTimeImmutable());
        $carpooling->setStatus(CarpoolingStatus::OPEN);

        // Autres champs
        $carpooling->setDeparturePlace($data['departurePlace'] ?? null);
        $carpooling->setArrivalPlace($data['arrivalPlace'] ?? null);
        $carpooling->setSeatCount($data['availableSeats'] ?? null);
        $carpooling->setPricePerPerson($data['pricePerPassenger'] ?? null);
        $carpooling->setIsEco($data['isEco'] ?? false);
        $carpooling->setStatus(CarpoolingStatus::tryFrom($data['status'] ?? CarpoolingStatus::OPEN->value));


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

        // Serialize with 'carpooling:read' group
        $responseData = $this->serializer->normalize($carpooling, 'json', ['groups' => 'carpooling:read']);
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
            // Serialize with specific groups for detailed view
            $responseData = $this->serializer->normalize($carpooling, 'json', [
                'groups' => [
                    'carpooling:read',
                    'car:read',
                    'brand:read',
                    'user:read'
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

        // Serialize with 'carpooling:read' group
        return $this->json($results, 200, [], ['groups' => 'carpooling:read']);
    }


    #[Route('/{id}', name: 'edit', requirements: ['id' => '\d+'], methods: ['PUT'])]
    public function edit(int $id, Request $request): JsonResponse
    {
        $carpooling = $this->repository->find($id);

        if (!$carpooling) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        // MODIFIÉ : La désérialisation pour l'édition doit aussi gérer les dates séparées
        $data = json_decode($request->getContent(), true);

        // Mise à jour des champs de date/heure
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

        // Mise à jour des autres champs manuellement ou avec le sérialiseur pour les autres
        // Si tu veux que le sérialiseur gère le reste, tu peux le faire ainsi:
        $this->serializer->deserialize(
            $request->getContent(),
            Carpooling::class,
            'json',
            [
                AbstractNormalizer::OBJECT_TO_POPULATE => $carpooling,
                // Ignorer les champs de date/heure car nous les gérons manuellement
                AbstractNormalizer::IGNORED_ATTRIBUTES => ['departureDate', 'departureTime', 'arrivalDate', 'arrivalTime', 'car']
            ]
        );
        // Assure-toi de re-setter la voiture si elle peut être changée, ou ignorée si elle ne l'est pas
        if (isset($data['car'])) {
            $car = $this->manager->getRepository(Car::class)->find($data['car']);
            if ($car) {
                $carpooling->setCar($car);
            }
        }
        // Mise à jour des autres champs qui ne sont pas gérés par le sérialiseur (comme seatCount, pricePerPerson, etc.)
        $carpooling->setDeparturePlace($data['departurePlace'] ?? $carpooling->getDeparturePlace());
        $carpooling->setArrivalPlace($data['arrivalPlace'] ?? $carpooling->getArrivalPlace());
        $carpooling->setSeatCount($data['availableSeats'] ?? $carpooling->getSeatCount());
        $carpooling->setPricePerPerson($data['pricePerPassenger'] ?? $carpooling->getPricePerPerson());
        $carpooling->setIsEco($data['isEco'] ?? $carpooling->isEco());
        $carpooling->setStatus(CarpoolingStatus::tryFrom($data['status'] ?? $carpooling->getStatus()->value));


        $carpooling->setUpdatedAt(new \DateTimeImmutable());

        $this->manager->flush();

        // Serialize with 'carpooling:read' group
        $responseData = $this->serializer->normalize($carpooling, 'json', ['groups' => 'carpooling:read']);
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
