<?php

namespace App\Tests\Controller;

use App\Entity\Car;
use App\Entity\Carpooling;
use App\Entity\Brand;
use App\Entity\User;
use App\Enum\CarpoolingStatus;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CarpoolingControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;
    private $user;
    private $brand;
    private $car;
    private $carpooling;
    private const TEST_USER_EMAIL = 'john.doe@example.com';
    private const TEST_USER_PASSWORD = 'password123';
    private const TEST_USER_API_TOKEN = 'a_super_secret_test_api_token_john_doe_12345';
    private const TEST_API_ROUTE = '/api/carpooling/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine.orm.entity_manager');

        // Ordre de nettoyage correct : Carpooling -> Car -> User
        $this->removeEntities(Carpooling::class);
        $this->removeEntities(Car::class);
        $this->removeEntities(User::class);
        // Pas de nettoyage de Brand ici car tu la gères manuellement

        $this->setUpTestUserCarAndCarpooling();
    }

    protected function tearDown(): void
    {
        // Nettoyage après chaque test pour assurer un état propre
        $this->removeEntities(Carpooling::class);
        $this->removeEntities(Car::class);
        $this->removeEntities(User::class);
        // Pas de nettoyage de Brand ici non plus

        parent::tearDown();
        if ($this->entityManager) {
            $this->entityManager->close();
            $this->entityManager = null;
        }
    }

    private function removeEntities(string $entityClass): void
    {
        $entities = $this->entityManager->getRepository($entityClass)->findAll();
        foreach ($entities as $entity) {
            $this->entityManager->remove($entity);
        }
        $this->entityManager->flush();
        $this->entityManager->clear(); // Clear l'entity manager après chaque flush
    }

    private function setUpTestUserCarAndCarpooling(): void
    {
        // Création de l'utilisateur
        $this->user = (new User())
            ->setUsername('JD123')
            ->setEmail(self::TEST_USER_EMAIL)
            ->setApiToken(self::TEST_USER_API_TOKEN)
            ->setRoles(['ROLE_USER'])
            ->setLastname('Doe')
            ->setFirstname('John')
            ->setCredits(20)
            ->setCreatedAt(new DateTimeImmutable());

        // Hacher le mot de passe via le service UserPasswordHasherInterface
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $hashedPassword = $passwordHasher->hashPassword($this->user, self::TEST_USER_PASSWORD);
        $this->user->setPassword($hashedPassword);

        $this->entityManager->persist($this->user);
        $this->entityManager->flush(); // Flush l'utilisateur pour avoir son ID

        // Récupération de la marque existante
        $this->brand = $this->entityManager->getRepository(Brand::class)->findOneBy([]);
        if (!$this->brand) {
            // Si aucune marque n'est trouvée, cela peut causer des problèmes pour la création de la voiture.
            // Assure-toi d'avoir au moins une Brand en base de données de test.
            throw new \Exception("Aucune entité Brand trouvée. Assurez-vous d'avoir des données de marque dans la base de test.");
        }

        // Création de la voiture
        $this->car = (new Car())
            ->setModel('Test Model Carpooling')
            ->setColor('Blue')
            ->setLicencePlate('XYZ-789-AB')
            ->setEnergy('Electric')
            ->setFirstRegistrationDate('15/05/2021')
            ->setUser($this->user)
            ->setBrand($this->brand)
            ->setCreatedAt(new DateTimeImmutable());

        $this->entityManager->persist($this->car);
        $this->entityManager->flush(); // Flush la voiture pour avoir son ID

        // Création du covoiturage
        $this->carpooling = (new Carpooling())
            ->setDepartureDate(new \DateTime('2025-01-01'))
            ->setDepartureTime(new \DateTime('10:00:00'))
            ->setDeparturePlace('Lille')
            ->setArrivalDate(new \DateTime('2025-01-01'))
            ->setArrivalTime(new \DateTime('16:00:00'))
            ->setArrivalPlace('Marseille')
            ->setSeatCount(4)
            ->setPricePerPerson(50.0)
            ->setIsEco(false)
            ->setCar($this->car)
            ->setCreatedAt(new DateTimeImmutable())
            ->setStatus(CarpoolingStatus::OPEN);

        $this->entityManager->persist($this->carpooling);
        $this->entityManager->flush();
        $this->entityManager->clear(); // Vider l'entityManager après setUp
    }

    private function getCarpooling(): Carpooling
    {
        // Recharge le covoiturage depuis la DB pour éviter les problèmes de cache de l'EM
        $this->entityManager->clear();
        return $this->entityManager->getRepository(Carpooling::class)->find($this->carpooling->getId());
    }

    public function testNewCarpoolingCreation(): void
    {
        $carpoolingData = [
            "departureDate" => "2025-01-01",
            "departureTime" => "12:00",
            "departurePlace" => "Startville",
            "arrivalDate" => "2025-01-01",
            "arrivalTime" => "14:00",
            "arrivalPlace" => "Endcity",
            "seatCount" => 3,
            "pricePerPerson" => 15.50,
            "isEco" => true,
            "car" => $this->car->getId(),
        ];

        $this->client->request(
            'POST',
            self::TEST_API_ROUTE,
            [],
            [],
            ['HTTP_X-AUTH-TOKEN' => self::TEST_USER_API_TOKEN, 'CONTENT_TYPE' => 'application/json'],
            json_encode($carpoolingData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertJson($this->client->getResponse()->getContent());
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals('Startville', $responseData['departurePlace']);
    }

    // public function testNewCarpoolingWithoutAuthentication(): void
    // {
    //     $this->client->request(
    //         'POST',
    //         self::TEST_API_ROUTE,
    //         [],
    //         [],
    //         ['CONTENT_TYPE' => 'application/json'],
    //         json_encode(["car" => 1, "departurePlace" => "Placeholder"])
    //     );

    //     $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    // }

    public function testNewCarpoolingWithNonExistentCar(): void
    {
        $carpoolingData = [
            "departureDate" => "2025-01-01",
            "departureTime" => "12:00",
            "departurePlace" => "Startville",
            "arrivalDate" => "2025-01-01",
            "arrivalTime" => "14:00",
            "arrivalPlace" => "Endcity",
            "seatCount" => 3,
            "pricePerPerson" => 15.50,
            "isEco" => true,
            "car" => 99999, // ID de voiture inexistant
        ];

        $this->client->request(
            'POST',
            self::TEST_API_ROUTE,
            [],
            [],
            ['HTTP_X-AUTH-TOKEN' => self::TEST_USER_API_TOKEN, 'CONTENT_TYPE' => 'application/json'],
            json_encode($carpoolingData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('message', $responseData, 'La réponse JSON devrait contenir une clé "message" pour l\'erreur.');
        $this->assertStringContainsString('Car not found', $responseData['message'], 'Le message d\'erreur devrait indiquer que la voiture n\'a pas été trouvée.');
    }

    public function testShowCarpooling(): void
    {
        $carpooling = $this->getCarpooling();

        $this->client->request(
            'GET',
            self::TEST_API_ROUTE . $carpooling->getId(),
            [],
            [],
            ['HTTP_X-AUTH-TOKEN' => self::TEST_USER_API_TOKEN]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJson($this->client->getResponse()->getContent());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('id', $responseData, 'La réponse JSON devrait contenir une clé "id".');
        $this->assertArrayHasKey('departurePlace', $responseData, 'La réponse JSON devrait contenir une clé "departurePlace".');

        $this->assertEquals($carpooling->getId(), $responseData['id']);
        $this->assertEquals($carpooling->getDeparturePlace(), $responseData['departurePlace']);
    }

    public function testShowNonExistentCarpooling(): void
    {
        $this->client->request(
            'GET',
            self::TEST_API_ROUTE . '99999',
            [],
            [],
            ['HTTP_X-AUTH-TOKEN' => self::TEST_USER_API_TOKEN]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testEditCarpooling(): void
    {
        $carpooling = $this->getCarpooling();

        $updatedData = ["departurePlace" => "New Place"];

        $this->client->request(
            'PUT',
            self::TEST_API_ROUTE . $carpooling->getId(),
            [],
            [],
            ['HTTP_X-AUTH-TOKEN' => self::TEST_USER_API_TOKEN, 'CONTENT_TYPE' => 'application/json'],
            json_encode($updatedData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJson($this->client->getResponse()->getContent());
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('departurePlace', $responseData, 'La réponse JSON devrait contenir une clé "departurePlace".');
        $this->assertEquals('New Place', $responseData['departurePlace']);

        // Vérifie en base de données
        $this->entityManager->clear();
        $updatedCarpooling = $this->entityManager->getRepository(Carpooling::class)->find($carpooling->getId());
        $this->assertEquals('New Place', $updatedCarpooling->getDeparturePlace());
    }

    public function testEditNonExistentCarpooling(): void
    {
        $this->client->request(
            'PUT',
            self::TEST_API_ROUTE . '99999',
            [],
            [],
            ['HTTP_X-AUTH-TOKEN' => self::TEST_USER_API_TOKEN, 'CONTENT_TYPE' => 'application/json'],
            json_encode(['departurePlace' => 'Non Existent'])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testDeleteCarpooling(): void
    {
        $carpoolingId = $this->getCarpooling()->getId();

        $this->client->request(
            'DELETE',
            self::TEST_API_ROUTE . $carpoolingId,
            [],
            [],
            ['HTTP_X-AUTH-TOKEN' => self::TEST_USER_API_TOKEN]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        $this->entityManager->clear();
        $this->assertNull($this->entityManager->getRepository(Carpooling::class)->find($carpoolingId));
    }

    public function testDeleteNonExistentCarpooling(): void
    {
        $this->client->request(
            'DELETE',
            self::TEST_API_ROUTE . '99999',
            [],
            [],
            ['HTTP_X-AUTH-TOKEN' => self::TEST_USER_API_TOKEN]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }
}