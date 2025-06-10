<?php

namespace App\Tests\Controller;

use App\Entity\Car;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CarControllerTest extends WebTestCase
{
    private $client;
    private ?EntityManagerInterface $entityManager = null;
    private const TEST_API_ROUTE = '/api/car/';
    private const TEST_USER_EMAIL = 'john.doe@example.com';
    private const TEST_USER_PASSWORD = 'password123';
    private const TEST_USER_API_TOKEN = 'a_super_secret_test_api_token_john_doe_12345';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();
        $this->cleanUpTestUser();
        $this->cleanUpTestCar();
        $this->createTestUserInDb();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        if ($this->entityManager) {
            $this->entityManager->clear();
            $this->entityManager = null;
        }
    }

    /**
     * Delete test user if exists before test to ensure a clean state.
     */
    private function cleanUpTestUser(): void
    {
        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => self::TEST_USER_EMAIL]);
        if ($existingUser) {
            $this->entityManager->remove($existingUser);
            $this->entityManager->flush();
            $this->entityManager->clear();
        }
    }

    /**
     * Delete test car if exists before test to ensure a clean state.
     */
    private function cleanUpTestCar(): void
    {
        $existingCar = $this->entityManager->getRepository(Car::class)->findOneBy(['licencePlate' => 'ABC1234']);
        if ($existingCar) {
            $this->entityManager->remove($existingCar);
            $this->entityManager->flush();
            $this->entityManager->clear();
        }
    }

    /**
     * Creates a test user directly in the database with a predefined API token.
     */
    private function createTestUserInDb(): void
    {
        $user = new User();
        $user->setFirstName('John');
        $user->setLastName('Doe');
        $user->setUserName('john_doe_test');
        $user->setEmail(self::TEST_USER_EMAIL);

        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $hashedPassword = $passwordHasher->hashPassword($user, self::TEST_USER_PASSWORD);
        $user->setPassword($hashedPassword);

        $user->setCredits(100);
        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setRoles(['ROLE_USER']);
        $user->setApiToken(self::TEST_USER_API_TOKEN);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    /**
     * Extracts the 'id' from the JSON response and asserts its validity.
     * @return int The extracted ID.
     */
    private function extractIdFromResponse(): int
    {
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $responseData, "Response should contain an 'id' key.");
        $newCarId = $responseData['id'];
        $this->assertIsInt($newCarId, "The extracted ID should be an integer.");
        $this->assertGreaterThan(0, $newCarId, "The extracted ID should be greater than 0.");
        return $newCarId;
    }

    /**
     * Creates a car through the API using the authenticated test user.
     * @return int The ID of the newly created car.
     */
    private function createCarThroughApiAndGetId(): int
    {
    
        $data = [
            'brand_id' => 1,
            'firstRegistrationDate' => '01/01/2020',
            'model' => 'Test Model',
            'licencePlate' => 'ABC1234',
            'color' => 'Vert',
            'energy' => 'Essence',
        ];

        $this->client->request(
            'POST',
            self::TEST_API_ROUTE,
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-AUTH-TOKEN' => self::TEST_USER_API_TOKEN
            ],
            json_encode($data)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertJson($this->client->getResponse()->getContent());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('model', $responseData);
        $this->assertEquals('Test Model', $responseData['model']);
        $this->assertArrayHasKey('licencePlate', $responseData);
        $this->assertEquals('ABC1234', $responseData['licencePlate']);
        $this->assertArrayHasKey('id', $responseData);

        return $this->extractIdFromResponse();
    }

    /**
     * TEST : Check new car is created in DB.
     */
    public function testNewCarCreation(): void
    {
        $newCarId = $this->createCarThroughApiAndGetId();

        $this->entityManager->clear();
        $createdCar = $this->entityManager->getRepository(Car::class)->find($newCarId);

        $this->assertNotNull($createdCar, 'The car should be found in the database after creation.');
        $this->assertEquals('Test Model', $createdCar->getModel());
        $this->assertEquals('ABC1234', $createdCar->getLicencePlate());
        $this->assertEquals('Vert', $createdCar->getColor());
        $this->assertEquals('Essence', $createdCar->getEnergy());
        $this->assertEquals('01/01/2020', $createdCar->getFirstRegistrationDate());


        // Test GET request after creation
        $this->client->request(
            'GET',
            self::TEST_API_ROUTE . $newCarId,
            [],
            [],
            ['HTTP_X-AUTH-TOKEN' => self::TEST_USER_API_TOKEN]
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJson($this->client->getResponse()->getContent());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals($newCarId, $responseData['id']);
        $this->assertEquals('Test Model', $responseData['model']);
        $this->assertEquals('ABC1234', $responseData['licencePlate']);
        $this->assertEquals('Vert', $responseData['color']);
        $this->assertEquals('Essence', $responseData['energy']);
    }

    /**
     * TEST : Check if car exists with Id.
     */
    public function testShowCar(): void
    {
        $newCarId = $this->createCarThroughApiAndGetId();

        $this->client->request(
            'GET',
            self::TEST_API_ROUTE . $newCarId,
            [],
            [],
            ['HTTP_X-AUTH-TOKEN' => self::TEST_USER_API_TOKEN]
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $responseContent = $this->client->getResponse()->getContent();
        $this->assertNotEmpty($responseContent);
        $this->assertJson($responseContent);

        $responseData = json_decode($responseContent, true);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals($newCarId, $responseData['id']);
        $this->assertArrayHasKey('model', $responseData);
        $this->assertEquals('Test Model', $responseData['model']);
        $this->assertArrayHasKey('licencePlate', $responseData);
        $this->assertEquals('ABC1234', $responseData['licencePlate']);
        $this->assertArrayHasKey('color', $responseData);
        $this->assertEquals('Vert', $responseData['color']);
        $this->assertArrayHasKey('energy', $responseData);
        $this->assertEquals('Essence', $responseData['energy']);
    }

    /**
     * TEST : Edit an existing car.
     */
    public function testEditCar(): void
    {
        $newCarId = $this->createCarThroughApiAndGetId();

        $data = [
            'model' => 'Updated Model',
            'color' => 'Blue',
        ];

        $this->client->request(
            'PUT',
            self::TEST_API_ROUTE . $newCarId,
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-AUTH-TOKEN' => self::TEST_USER_API_TOKEN
            ],
            json_encode($data)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $responseContent = $this->client->getResponse()->getContent();
        $this->assertNotEmpty($responseContent);
        $this->assertJson($responseContent);

        $responseData = json_decode($responseContent, true);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals($newCarId, $responseData['id']);
        $this->assertArrayHasKey('model', $responseData);
        $this->assertEquals('Updated Model', $responseData['model']);
        $this->assertArrayHasKey('color', $responseData);
        $this->assertEquals('Blue', $responseData['color']);
        $this->assertArrayHasKey('licencePlate', $responseData);
        $this->assertEquals('ABC1234', $responseData['licencePlate']);

        $this->entityManager->clear();
        $updatedCar = $this->entityManager->getRepository(Car::class)->find($newCarId);
        $this->assertNotNull($updatedCar);
        $this->assertEquals('Updated Model', $updatedCar->getModel());
        $this->assertEquals('Blue', $updatedCar->getColor());
    }

    /**
     * TEST : Delete an existing car.
     */
    public function testDeleteCar(): void
    {
        $newCarId = $this->createCarThroughApiAndGetId();

        $this->client->request(
            'DELETE',
            self::TEST_API_ROUTE . $newCarId,
            [],
            [],
            ['HTTP_X-AUTH-TOKEN' => self::TEST_USER_API_TOKEN]
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $this->client->request(
            'GET',
            self::TEST_API_ROUTE . $newCarId,
            [],
            [],
            ['HTTP_X-AUTH-TOKEN' => self::TEST_USER_API_TOKEN]
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    /**
     * TEST: Attempt to create a car without authentication.
     */
    public function testCreateCarWithoutAuthentication(): void
    {
        $data = [
            'brand_id' => 1,
            'firstRegistrationDate' => '01/01/2020',
            'model' => 'Unauthorized Model',
            'licencePlate' => 'XYZ7890',
            'color' => 'Red',
            'energy' => 'Electric',
        ];

        $this->client->request(
            'POST',
            self::TEST_API_ROUTE,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        // Expecting 401 Unauthorized or 403 Forbidden
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * TEST: Attempt to show a car without authentication.
     */
    public function testShowCarWithoutAuthentication(): void
    {
        $newCarId = $this->createCarThroughApiAndGetId();

        $this->client->request('GET', self::TEST_API_ROUTE . $newCarId);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * TEST: Attempt to edit a car without authentication.
     */
    public function testEditCarWithoutAuthentication(): void
    {
        $newCarId = $this->createCarThroughApiAndGetId();

        $data = [
            'model' => 'Unauthorized Update'
        ];

        $this->client->request(
            'PUT',
            self::TEST_API_ROUTE . $newCarId,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * TEST: Attempt to delete a car without authentication.
     */
    public function testDeleteCarWithoutAuthentication(): void
    {
        $newCarId = $this->createCarThroughApiAndGetId();

        $this->client->request('DELETE', self::TEST_API_ROUTE . $newCarId);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }
}