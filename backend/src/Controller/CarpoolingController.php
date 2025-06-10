<?php

namespace App\Controller;

use App\Entity\Car;
use App\Entity\Carpooling;
use App\Enum\CarpoolingStatus;
use OpenApi\Attributes as OA;
use App\Repository\CarpoolingRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route as AnnotationRoute;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;


#[Route('api/carpooling', name: 'app_api_carpooling_')]
final class CarpoolingController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private CarpoolingRepository $repository,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator,
        private NormalizerInterface $normalizer,
        private Security $security
        )
    {
    }

    // New carpooling Route / API doc
    #[Route('/', name: 'new', methods: 'POST')]
    #[OA\Post(
        path: "/api/carpooling",
        summary: "Publish a new carpooling",
        requestBody: new OA\RequestBody(
            required: true,
            description: "Datas required to post",
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(property: "departureDate", type: "string", format: "date", example: "2025-01-01"),
                    new OA\Property(property: "departureTime", type: "string", format: "time", example: "12:00"),
                    new OA\Property(property: "departurePlace", type: "string", example: "Melun"),
                    new OA\Property(property: "arrivalDate", type: "string", format: "date", example: "2025-01-01"),
                    new OA\Property(property: "arrivalTime", type: "string", format: "time", example: "12:45"),
                    new OA\Property(property: "arrivalPlace", type: "string", example: "Paris"),
                    new OA\Property(property: "seatCount", type: "integer", example: 2),
                    new OA\Property(property: "pricePerPerson", type: "float", example: 5),
                    new OA\Property(property: "isEco", type: "boolean", example: true),
                    new OA\Property(property: "car", type: "integer", example: 1),

                ],
                required: [
                    "departureDate", "departureTime", "departurePlace",
                    "arrivalDate", "arrivalTime", "arrivalPlace", "seatCount",
                    "pricePerPerson", "isEco"
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Carpooling successfully published",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "departureDate", type: "string", format: "date", example: "2025-01-01"),
                        new OA\Property(property: "departureTime", type: "string", format: "time", example: "12:00"),
                        new OA\Property(property: "departurePlace", type: "string", example: "Melun"),
                        new OA\Property(property: "arrivalDate", type: "string", format: "date", example: "2025-01-01"),
                        new OA\Property(property: "arrivalTime", type: "string", format: "time", example: "12:45"),
                        new OA\Property(property: "arrivalPlace", type: "string", example: "Paris"),
                        new OA\Property(property: "seatCount", type: "integer", example: 2),
                        new OA\Property(property: "pricePerPerson", type: "float", example: 5),
                        new OA\Property(property: "isEco", type: "boolean", example: true),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: "Invalid data",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Invalid data provided")
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Unauthorized",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Unauthorized access")
                    ]
                )
            )
        ]
    )]
    // New carpooling function
    public function new(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $car = $this->manager->getRepository(Car::class)->find($data['car']);

        if (!$car) {
            return new JsonResponse(['message' => 'Car not found'], Response::HTTP_BAD_REQUEST);
        }

        $carpooling = $this->serializer->deserialize(
            $request->getContent(),
            Carpooling::class,
            'json',
            [AbstractNormalizer::IGNORED_ATTRIBUTES => ['car']]
        );
        $carpooling->setCar($car);
        $carpooling->setCreatedAt(new DateTimeImmutable());
        $carpooling->setStatus(CarpoolingStatus::OPEN);


        //Implémenter logique(formulaire)

        $this->manager->persist($carpooling);
        $this->manager->flush();
        
        // CORRECTION ICI : Utilise normalize pour obtenir un tableau PHP, puis JsonResponse l'encode
        $responseData = $this->serializer->normalize($carpooling, 'json', ['groups' => 'carpooling_read']);
        $location = $this->urlGenerator->generate(
            name: 'app_api_carpooling_show',
            parameters: ['id' => $carpooling->getId()],
            referenceType: UrlGeneratorInterface::ABSOLUTE_URL
        );

        return new JsonResponse(
            data: $responseData, // Passe le tableau PHP normalisé
            status: Response::HTTP_CREATED,
            headers: ["Location" => $location]
            // Supprimez 'json: true' car JsonResponse détecte automatiquement que c'est un tableau PHP
        );
    }

    #[Route('/{id}', name: 'show', methods: 'GET')]
    public function show(int $id): JsonResponse
    {
        $carpooling = $this->repository->findOneBy(['id' => $id]);

        if ($carpooling) {
            // CORRECTION ICI : Utilise normalize pour obtenir un tableau PHP
            $responseData = $this->serializer->normalize($carpooling, 'json', ['groups' => 'carpooling_read']);
            return new JsonResponse($responseData, status: Response::HTTP_OK);
        }

        return new JsonResponse(data: null, status: Response::HTTP_NOT_FOUND);
    }

    #[Route('/{id}', name: 'edit', methods: 'PUT')]
    public function edit(int $id, Request $request): JsonResponse
    {
        $carpooling = $this->repository->find($id);

        if (!$carpooling) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        $this->serializer->deserialize(
            $request->getContent(),
            Carpooling::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $carpooling]
        );

        $carpooling->setUpdatedAt(new DateTimeImmutable());

        $this->manager->flush();

        // CORRECTION ICI : Utilise normalize pour obtenir un tableau PHP
        $responseData = $this->serializer->normalize($carpooling, 'json', ['groups' => 'carpooling_read']);
        $location = $this->urlGenerator->generate(
            'app_api_carpooling_show',
            ['id' => $carpooling->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return new JsonResponse(
            data: $responseData, // Passe le tableau PHP normalisé
            status: Response::HTTP_OK,
            headers: ['Location' => $location]
            // Supprimez 'json: true'
        );
    }


    #[Route('/{id}', name: 'delete', methods: 'DELETE')]
    public function delete(int $id): JsonResponse
    {
        $carpooling = $this->repository->findOneBy(['id' => $id]);

        if ($carpooling) {
            $this->manager->remove($carpooling);
            $this->manager->flush();

            return new JsonResponse(null, status: Response::HTTP_NO_CONTENT);
        }

        return new JsonResponse(null, status: Response::HTTP_NOT_FOUND);
    }
}