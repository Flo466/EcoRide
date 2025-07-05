<?php

namespace App\Controller;

use App\Repository\BrandRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Psr\Log\LoggerInterface; // Import LoggerInterface if you want to use logging

#[Route('/api', name: 'app_api_')]
class BrandController extends AbstractController
{
    private BrandRepository $brandRepository;
    private SerializerInterface $serializer;
    private LoggerInterface $logger; // Inject LoggerInterface

    public function __construct(BrandRepository $brandRepository, SerializerInterface $serializer, LoggerInterface $logger)
    {
        $this->brandRepository = $brandRepository;
        $this->serializer = $serializer;
        $this->logger = $logger; // Assign logger
    }

    /**
     * Retrieves all car brands from the database.
     * Accessible via GET /api/brands
     */
    #[Route('/brands', name: 'get_brands', methods: ['GET'])]
    #[IsGranted('ROLE_USER')] // Ensure only authenticated users can access this
    public function getBrands(): JsonResponse
    {
        $this->logger->info('Attempting to fetch all car brands.');

        try {
            // Fetch all Brand entities from the database
            $brands = $this->brandRepository->findAll();

            // Serialize the brands into JSON format
            // Corrected: Using 'brand_read' group to match the Brand entity annotation
            $jsonBrands = $this->serializer->serialize($brands, 'json', ['groups' => ['brand_read']]);

            $this->logger->info('Car brands fetched and serialized successfully.', ['count' => count($brands)]);

            // Return the JSON response
            return new JsonResponse($jsonBrands, Response::HTTP_OK, [], true);
        } catch (\Exception $e) {
            $this->logger->error('Error fetching car brands.', [
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return new JsonResponse(['message' => 'An error occurred while fetching car brands.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
