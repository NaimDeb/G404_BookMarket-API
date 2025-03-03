<?php

namespace App\Tests\Functional\Controller;

use App\Entity\User;
use App\Entity\ProfessionalDetails;
use App\Entity\UserDetails;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()
            ->get('doctrine')
            ->getManager();
        $this->entityManager->beginTransaction();
        $this->passwordHasher = $this->client->getContainer()->get(UserPasswordHasherInterface::class);
    }

    public function testApiMeEndpointWithoutAuthentication()
    {
        $this->client->request('GET', '/api/me');

        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());
    }

    public function testApiMeEndpointWithRegularUser()
    {
        // Call /api/register endpoint
        $this->client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/ld+json'],
            json_encode([
                'email' => 'test@test.com',
                'password' => 'password123',
                'username' => 'testuser',
                'profileDesc' => 'Test user profile description',
                'userDetails' => [
                    'address' => '123 Test St',
                    'phone' => '123-456-7890',
                    'country' => 'Testland',
                    'firstName' => 'Test',
                    'lastName' => 'User'
                ]
            ])
        );

        $this->assertEquals(201, $this->client->getResponse()->getStatusCode(), "Registration failed: " . $this->client->getResponse()->getContent());

        $this->entityManager->commit();



        // Gets JWT token
        $this->client->request(
            'POST',
            '/api/login_check',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'test@test.com',
                'password' => 'password123'
            ])
        );

        // Add these debug lines
        $loginResponse = $this->client->getResponse();
        $this->assertEquals(200, $loginResponse->getStatusCode(), 'Login failed: ' . $loginResponse->getContent());

        $data = json_decode($loginResponse->getContent(), true);
        $this->assertArrayHasKey('token', $data, 'No token in response: ' . $loginResponse->getContent());
        $token = $data['token'];

        // Test /api/me endpoint
        $this->client->request(
            'GET',
            '/api/me',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
                'CONTENT_TYPE' => 'application/ld+json'
            ]
        );

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "Me endpoint failed: " . $this->client->getResponse()->getContent());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        // Checks if the response gives the same email as the user connected
        $this->assertEquals('test@test.com', $responseData['email'], "Me endpoint failed: The email from the response doesn't match the one from the user connected");
        // Checks if the response gives the same userName as the user connected
        $this->assertEquals('testuser', $responseData['username'], "Me endpoint failed: The userName from the response doesn't match the one from the user connected");
        // Checks if the response has a userDetails
        $this->assertArrayHasKey('userDetails', $responseData, "Me endpoint failed: The response doesn't have a userDetails");
        // Checks if the response doesn't have a professionalDetails
        $this->assertArrayNotHasKey('professionalDetails', $responseData, "Me endpoint failed: The response has a professionalDetails");
    }

    public function testApiMeEndpointWithProUser()
    {
        // Call /api/register endpoint
        $this->client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/ld+json'],
            json_encode([
                'email' => 'test@test.com',
                'password' => 'password123',
                'username' => 'testuser',
                'profileDesc' => 'Test user profile description',
                'userDetails' => [
                    'address' => '123 Test St',
                    'phone' => '123-456-7890',
                    'country' => 'Testland',
                    'firstName' => 'Test',
                    'lastName' => 'User'
                ]
            ])
        );

        $this->assertEquals(201, $this->client->getResponse()->getStatusCode(), "Registration failed: " . $this->client->getResponse()->getContent());

        $this->entityManager->commit();



        // Gets JWT token
        $this->client->request(
            'POST',
            '/api/login_check',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'test@test.com',
                'password' => 'password123'
            ])
        );

        // Add these debug lines
        $loginResponse = $this->client->getResponse();
        $this->assertEquals(200, $loginResponse->getStatusCode(), 'Login failed: ' . $loginResponse->getContent());

        $data = json_decode($loginResponse->getContent(), true);
        $this->assertArrayHasKey('token', $data, 'No token in response: ' . $loginResponse->getContent());
        $token = $data['token'];

        // Test /api/me endpoint
        $this->client->request(
            'GET',
            '/api/me',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
                'CONTENT_TYPE' => 'application/ld+json'
            ]
        );

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "Me endpoint failed: " . $this->client->getResponse()->getContent());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        // Checks if the response gives the same email as the user connected
        $this->assertEquals('test@test.com', $responseData['email'], "Me endpoint failed: The email from the response doesn't match the one from the user connected");
        // Checks if the response gives the same userName as the user connected
        $this->assertEquals('testuser', $responseData['username'], "Me endpoint failed: The userName from the response doesn't match the one from the user connected");
        // Checks if the response has a userDetails
        $this->assertArrayHasKey('userDetails', $responseData, "Me endpoint failed: The response doesn't have a userDetails");
        // // Gets user details id from the response
        // $userDetailsId = $responseData['userDetails']['id'];

        // // Gets user details from database
        // $userDetails = $this->entityManager->getRepository(UserDetails::class)->find($userDetailsId);

        // // Checks if the user details in the response has the same user as the one connected
        // $this->assertEquals($userDetails->getUser()->getId(), $responseData['userDetails']['id'], "Me endpoint failed: The user details from the response doesn't have the same user as the one from the user connected");

        // Checks if the response doesn't have a professionalDetails
        $this->assertArrayHasKey('professionalDetails', $responseData, "Me endpoint failed: The response doesn't have a professionalDetails");
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Rollback transaction
        if ($this->entityManager->getConnection()->isTransactionActive()) {
            $this->entityManager->getConnection()->rollback();
        }

        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'test@test.com']);

        if ($user) {
            $this->entityManager->remove($user);
            $this->entityManager->flush();
        }
    }
}
