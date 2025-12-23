<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\Uuid;

class SecurityControllerTest extends WebTestCase
{
    private $client;
    private ?EntityManagerInterface $entityManager = null;
    private const TEST_USER_EMAIL = 'john.doe@example.com';
    private const TEST_USER_PASSWORD = 'password123';
    private const TEST_USER_API_TOKEN = 'a_super_secret_test_api_token_john_doe_12345';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();
        $this->cleanUpTestUser();
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
     * Creates a test user by making a POST request to the API's registration endpoint.
     * This method also asserts the success of the registration.
     * @return int The ID of the newly created user.
     */
    private function createTestUserThroughApi(): int
    {
        $data = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'userName' => 'john_doe_test',
            'email' => self::TEST_USER_EMAIL,
            'password' => self::TEST_USER_PASSWORD,
            'credits' => 100,
        ];

        $this->client->request(
            'POST',
            '/api/registration',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertJson($this->client->getResponse()->getContent());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('email', $responseData);
        $this->assertEquals(self::TEST_USER_EMAIL, $responseData['email']);

        return $responseData['id'];
    }

    /**
     * Creates a test user directly in the database with a predefined API token.
     * This is useful for tests that require an authenticated user without relying on the /api/registration endpoint.
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
     * Logs in a test user via the API's login endpoint and returns the decoded JSON response.
     * @return array The decoded contents of the login JSON response, typically including the apiToken.
     */
    private function loginTestUserThroughApi(): array
    {
        $data = [
            'email' => self::TEST_USER_EMAIL,
            'password' => self::TEST_USER_PASSWORD,
        ];

        $this->client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJson($this->client->getResponse()->getContent());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('email', $responseData);
        $this->assertArrayHasKey('apiToken', $responseData);
        $this->assertEquals(self::TEST_USER_EMAIL, $responseData['email']);


        return $responseData;
    }

    // --- PUBLIC TESTS ---

    public function testRegister(): void
    {
        $this->createTestUserThroughApi();
    }

    public function testLogin(): void
    {
        $this->createTestUserInDb();
        $this->loginTestUserThroughApi();
    }

    public function testMe(): void
    {
        $this->createTestUserInDb();
        $token = self::TEST_USER_API_TOKEN;

        $this->client->request(
            'GET',
            '/api/account/me',
            [],
            [],
            ['HTTP_X-AUTH-TOKEN' => $token]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJson($this->client->getResponse()->getContent());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('email', $responseData);
        $this->assertEquals(self::TEST_USER_EMAIL, $responseData['email']);
        $this->assertArrayHasKey('firstName', $responseData);
        $this->assertEquals('John', $responseData['firstName']);
        $this->assertArrayHasKey('apiToken', $responseData);
        $this->assertEquals($token, $responseData['apiToken']);
        $this->assertArrayHasKey('userName', $responseData);
        $this->assertEquals('john_doe_test', $responseData['userName']);
        $this->assertArrayHasKey('credits', $responseData);
        $this->assertEquals(100, $responseData['credits']);
    }

    public function testEdit(): void
    {
        $this->createTestUserInDb();
        $userToEdit = $this->entityManager->getRepository(User::class)->findOneBy(['email' => self::TEST_USER_EMAIL]);
        $this->assertNotNull($userToEdit, 'L\'utilisateur à éditer doit exister.');

        $userId = $userToEdit->getId();
        $token = self::TEST_USER_API_TOKEN;

        $dataToUpdate = [
            'firstName' => 'Jane',
            'lastName' => 'Doe-Edited',
            'phone' => '0612345678',
        ];

        $this->client->request(
            'PUT',
            '/api/account/edit',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-AUTH-TOKEN' => $token
            ],
            json_encode($dataToUpdate)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJson($this->client->getResponse()->getContent());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals($userId, $responseData['id']);
        $this->assertArrayHasKey('firstName', $responseData);
        $this->assertEquals('Jane', $responseData['firstName']);
        $this->assertArrayHasKey('lastName', $responseData);
        $this->assertEquals('Doe-Edited', $responseData['lastName']);
        $this->assertArrayHasKey('phone', $responseData);
        $this->assertEquals('0612345678', $responseData['phone']);
        $this->assertArrayHasKey('email', $responseData);
        $this->assertEquals(self::TEST_USER_EMAIL, $responseData['email']);
        $this->assertArrayHasKey('apiToken', $responseData);
        $this->assertEquals($token, $responseData['apiToken']);

        // OPTIONAL: Verify changes directly in the database to ensure persistence
        $this->entityManager->clear();
        $updatedUser = $this->entityManager->getRepository(User::class)->find($userId);
        $this->assertNotNull($updatedUser);
        $this->assertEquals('Jane', $updatedUser->getFirstName());
        $this->assertEquals('Doe-Edited', $updatedUser->getLastName());
        $this->assertEquals('0612345678', $updatedUser->getPhone());
    }
}