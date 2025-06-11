<?php

namespace App\Tests\Controller;

use App\Entity\Review;
use App\Entity\User;
use App\Enum\ReviewStatus;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use DateTimeImmutable;

class ReviewControllerTest extends WebTestCase
{
    private $client;
    private ?EntityManagerInterface $entityManager = null;
    private ?Connection $connection = null;
    private const TEST_API_ROUTE = '/api/review/';
    private string $uniqueTestUserEmail;
    private string $uniqueTestUserApiToken;
    private const TEST_USER_PASSWORD = 'password123';

    private ?User $testUser = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();
        $this->connection = $this->entityManager->getConnection();

        $this->connection->beginTransaction();

        $this->uniqueTestUserEmail = 'john.doe.' . uniqid() . '@example.com';
        $this->uniqueTestUserApiToken = 'test_token_' . uniqid() . bin2hex(random_bytes(8));


        $this->testUser = $this->createTestUserInDb();
        $this->assertNotNull($this->testUser, 'L\'utilisateur de test n\'a pas pu être créé.');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if ($this->connection && $this->connection->isTransactionActive()) {
            try {
                $this->connection->rollBack();
            } catch (\Exception $e) {
                // Gérer les erreurs de rollback si nécessaire, bien que rares en test
            }
        }

        if ($this->entityManager) {
            $this->entityManager->close();
            $this->entityManager = null;
        }

        static::ensureKernelShutdown();

        $this->connection = null;
        $this->testUser = null;
    }

    /**
     * Creates a test user directly in the database with a predefined API token.
     * @return User The managed User entity.
     */
    private function createTestUserInDb(): User
    {
        $user = new User();
        $user->setFirstName('John');
        $user->setLastName('Doe');
        $user->setUserName('john_doe_test');
        $user->setEmail($this->uniqueTestUserEmail);

        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $hashedPassword = $passwordHasher->hashPassword($user, self::TEST_USER_PASSWORD);
        $user->setPassword($hashedPassword);

        $user->setCredits(100);
        $user->setCreatedAt(new DateTimeImmutable());
        $user->setRoles(['ROLE_USER']);
        $user->setApiToken($this->uniqueTestUserApiToken);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
        return $user;
    }

    /**
     * Extracts the 'id' from the JSON response and asserts its validity.
     * @return int The extracted ID.
     */
    private function extractIdFromResponse(): int
    {
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $responseData, 'La réponse JSON de l\'API devrait contenir une clé "id".');
        $newReviewId = $responseData['id'];
        $this->assertIsInt($newReviewId);
        $this->assertGreaterThan(0, $newReviewId);
        return $newReviewId;
    }

    /**
     * Creates a review through the API using the authenticated test user.
     * @return int The ID of the newly created review.
     */
    private function createReviewThroughApiAndGetId(): int
    {
        $data = [
            'comment' => 'Test Comment for Review',
            'ratting' => 5,
        ];

        $this->client->request(
            'POST',
            self::TEST_API_ROUTE,
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-AUTH-TOKEN' => $this->uniqueTestUserApiToken
            ],
            json_encode($data)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED,);
        $responseContent = $this->client->getResponse()->getContent();
        $this->assertJson($responseContent);

        $responseData = json_decode($responseContent, true);
        $this->assertArrayHasKey('comment', $responseData);
        $this->assertEquals('Test Comment for Review', $responseData['comment']);
        $this->assertArrayHasKey('ratting', $responseData);
        $this->assertEquals(5, $responseData['ratting']);
        $this->assertArrayHasKey('id', $responseData);

        return $this->extractIdFromResponse();
    }

    /**
     * TEST : Check new review is created in DB.
     */
    public function testNewReviewCreation(): void
    {
        $newReviewId = $this->createReviewThroughApiAndGetId();

        $this->entityManager->clear();
        $createdReview = $this->entityManager->getRepository(Review::class)->find($newReviewId);

        $this->assertNotNull($createdReview, 'La review créée devrait être trouvée en base de données.');
        $this->assertEquals('Test Comment for Review', $createdReview->getComment());
        $this->assertEquals(5, $createdReview->getRatting());
        
        $reloadedTestUser = $this->entityManager->getRepository(User::class)->find($this->testUser->getId());
        $this->assertNotNull($reloadedTestUser, 'L\'utilisateur de test rechargé ne devrait pas être null.');

        $this->assertEquals($reloadedTestUser->getId(), $createdReview->getUser()->getId(), 'La review devrait être associée à l\'utilisateur de test.');

        $this->client->request(
            'GET',
            self::TEST_API_ROUTE . $newReviewId,
            [],
            [],
            []
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK, 'La récupération d\'une review devrait retourner 200 OK.');
        $this->assertJson($this->client->getResponse()->getContent(), 'La réponse de récupération de review devrait être du JSON valide.');

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals($newReviewId, $responseData['id']);
        $this->assertEquals('Test Comment for Review', $responseData['comment']);
        $this->assertEquals(5, $responseData['ratting']);
        $this->assertEquals(ReviewStatus::PENDING->value, $responseData['status']);
        $this->assertArrayHasKey('user', $responseData);
        $this->assertEquals($reloadedTestUser->getId(), $responseData['user']['id'], 'L\'utilisateur dans la réponse JSON devrait correspondre à l\'utilisateur de test.');
    }

    /**
     * TEST : Check if review exists with Id.
     */
     public function testShowReview(): void
    {
        $newReviewId = $this->createReviewThroughApiAndGetId();

        $this->client->request(
            'GET',
            self::TEST_API_ROUTE . $newReviewId,
            [],
            [],
            []
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK, 'Expected 200 OK for showing review.');

        $responseContent = $this->client->getResponse()->getContent();
        $this->assertNotEmpty($responseContent, 'Response content should not be empty.');
        $this->assertJson($responseContent, 'Response content should be valid JSON.');

        $responseData = json_decode($responseContent, true);
        $this->assertIsArray($responseData, 'Decoded response should be an array.');
        $this->assertArrayHasKey('id', $responseData, "JSON response should have 'id' key.");
        $this->assertEquals($newReviewId, $responseData['id']);
        $this->assertArrayHasKey('comment', $responseData);
        $this->assertEquals('Test Comment for Review', $responseData['comment']);
        $this->assertArrayHasKey('ratting', $responseData);
        $this->assertEquals(5, $responseData['ratting']);
        $this->assertArrayHasKey('status', $responseData);
        $this->assertEquals(ReviewStatus::PENDING->value, $responseData['status']);
        $this->assertArrayHasKey('user', $responseData);
        $this->assertArrayHasKey('id', $responseData['user']);
    }

    /**
     * TEST : Update an existing review's status.
     */
    public function testUpdateReviewStatus(): void
    {
        $newReviewId = $this->createReviewThroughApiAndGetId();

        $data = [
            'status' => ReviewStatus::APPROVED->value,
        ];

        $this->client->request(
            'PATCH',
            self::TEST_API_ROUTE . $newReviewId . '/status',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-AUTH-TOKEN' => $this->uniqueTestUserApiToken
            ],
            json_encode($data)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK, 'La mise à jour du statut devrait retourner 200 OK.');
        $responseContent = $this->client->getResponse()->getContent();
        $this->assertNotEmpty($responseContent);
        $this->assertJson($responseContent);

        $responseData = json_decode($responseContent, true);
        $this->assertArrayHasKey('status', $responseData);
        $this->assertEquals(ReviewStatus::APPROVED->value, $responseData['status']);

        $this->entityManager->clear();
        $updatedReview = $this->entityManager->getRepository(Review::class)->find($newReviewId);
        $this->assertNotNull($updatedReview);
        $this->assertEquals(ReviewStatus::APPROVED, $updatedReview->getStatus());
    }

    /**
     * TEST : Delete an existing review.
     */
    public function testDeleteReview(): void
    {
        $newReviewId = $this->createReviewThroughApiAndGetId();

        $this->client->request(
            'DELETE',
            self::TEST_API_ROUTE . $newReviewId,
            [],
            [],
            ['HTTP_X-AUTH-TOKEN' => $this->uniqueTestUserApiToken]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT, 'Expected 204 No Content for successful deletion.');

        $this->entityManager->clear();
        $deletedReview = $this->entityManager->getRepository(Review::class)->find($newReviewId);
        $this->assertNull($deletedReview);
    }

    /**
     * TEST: Attempt to create a review without authentication.
     */
    public function testCreateReviewWithoutAuthentication(): void
    {
        $data = [
            'comment' => 'Unauthorized Comment',
            'ratting' => 1,
        ];

        $this->client->request(
            'POST',
            self::TEST_API_ROUTE,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * TEST: Attempt to show a review without authentication.
     */
    public function testShowReviewWithoutAuthentication(): void
    {
        $newReviewId = $this->createReviewThroughApiAndGetId();

        $this->client->request('GET', self::TEST_API_ROUTE . $newReviewId);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * TEST: Attempt to update review status without authentication.
     */
    public function testUpdateReviewStatusWithoutAuthentication(): void
    {
        $newReviewId = $this->createReviewThroughApiAndGetId();

        $data = [
            'status' => ReviewStatus::REJECTED->value,
        ];

        $this->client->request(
            'PATCH',
            self::TEST_API_ROUTE . $newReviewId . '/status',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * TEST: Attempt to delete a review without authentication.
     */
    public function testDeleteReviewWithoutAuthentication(): void
    {
        $newReviewId = $this->createReviewThroughApiAndGetId();

        $this->client->request('DELETE', self::TEST_API_ROUTE . $newReviewId);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }
}
