<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    // Create and register an user
    public function testRegister(): void
    {
        $data = [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'userName' => 'john_doe',
            'email' => 'john.doe@example.com',
            'password' => 'password123',
        ];

        $this->client->request(
            'POST',
            '/api/registration',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $this->assertResponseStatusCodeSame(201);
        $this->assertJson($this->client->getResponse()->getContent());
    }

    // Test de la route de connexion
    public function testLogin(): void
    {
        // An user nedd to be create first

        // Get request login
        $data = [
            'email' => 'john.doe@example.com',
            'password' => 'password123',
        ];

        $this->client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $this->assertResponseStatusCodeSame(200);
        $this->assertJson($this->client->getResponse()->getContent());
    }

    // Test get user
    public function testMe(): void
    {
        $this->client->loginUser($this->getTestUser());

        $this->client->request('GET', '/api/account/me');

        $this->assertResponseStatusCodeSame(200);
        $this->assertJson($this->client->getResponse()->getContent());
    }

    // Test editing user
    public function testEdit(): void
    {
        $this->client->loginUser($this->getTestUser());

        $data = [
            'firstName' => 'Jane',
        ];

        $this->client->request(
            'PUT',
            '/api/account/edit',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $this->assertResponseStatusCodeSame(200);
        $this->assertJson($this->client->getResponse()->getContent());
    }

    private function getTestUser()
    {
        // Retourne un utilisateur spécifique ou crée un utilisateur fictif pour les tests
        return $this->getContainer()->get('doctrine')->getRepository(User::class)->findOneBy(['email' => 'john.doe@example.com']);
    }
}
