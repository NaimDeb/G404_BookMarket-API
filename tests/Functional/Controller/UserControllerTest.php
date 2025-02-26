<?php

namespace App\Tests\Functional\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class UserControllerTest extends WebTestCase
{
    private $client;
    private $testUserEmail;
    private $entityManager;
    private $testUserPassword = 'password123';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $this->testUserEmail = 'test' . uniqid() . '@example.com';
    }

    public function testRegisterUser(): array
    {
        $userData = [
            'email' => $this->testUserEmail,
            'password' => $this->testUserPassword,
            'username' => 'user_' . uniqid(),
            'profileDesc' => 'Test user description',
            'userDetails' => [
                'address' => '123 Test Street',
                'phone' => '1234567890',
                'country' => 'Test Country',
                'firstName' => 'Test',
                'lastName' => 'User'
            ],
            'professionalDetails' => [
                'companyName' => 'Test Company',
                'companyAddress' => '456 Company Street',
                'companyPhone' => '0987654321'
            ]
        ];

        $this->client->request(
            'POST',
            '/api/register',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/ld+json',
                'HTTP_ACCEPT' => 'application/ld+json'
            ],
            json_encode($userData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        // Return credentials needed for login
        return [
            'username' => $this->testUserEmail,
            'password' => $this->testUserPassword
        ];
    }


    public function testMeEndpointWithoutAuth(): void
    {
        $this->client->request(
            'GET',
            '/api/me',
            [],
            [],
            ['CONTENT_TYPE' => 'application/ld+json']
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }


    /**
 * @depends testRegisterUser
 */
public function testMeEndpointWithAuth(array $credentials): void
{
    // Debug credentials
    echo "\nTrying to login with credentials: " . json_encode($credentials);

    // First, login to get the token
    $this->client->request(
        'POST',
        '/api/login_check',
        [],
        [],
        [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json'
        ],
        json_encode([
            'username' => $credentials['username'],  // This should be the email
            'password' => $credentials['password']
        ])
    );

    // Debug login response
    echo "\nLogin response: " . $this->client->getResponse()->getContent();

    // Verify the database state
    $user = $this->entityManager->getRepository(User::class)
        ->findOneBy(['email' => $credentials['username']]);
    
    echo "\nUser in database: " . ($user ? "YES" : "NO");
    if ($user) {
        echo "\nUser roles: " . json_encode($user->getRoles());
        echo "\nUser email: " . $user->getEmail();
    }

    $this->assertResponseIsSuccessful();
    $loginResponse = json_decode($this->client->getResponse()->getContent(), true);
    $this->assertArrayHasKey('token', $loginResponse, 'Login response should contain a token');


        // Now use the token to access /api/me
        $this->client->request(
            'GET',
            '/api/me',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $loginResponse['token'],
                'CONTENT_TYPE' => 'application/ld+json'
            ]
        );

        $this->assertResponseIsSuccessful();
        $meResponse = json_decode($this->client->getResponse()->getContent(), true);
        
        // Assert the response contains the expected user data
        $this->assertEquals($credentials['username'], $meResponse['email']);
    }

    protected function tearDown(): void
    {
        if ($this->testUserEmail) {
            $user = $this->entityManager->getRepository(User::class)
                ->findOneBy(['email' => $this->testUserEmail]);

            if ($user) {
                $this->entityManager->remove($user);
                $this->entityManager->flush();
            }
        }

        $this->entityManager->close();
        $this->entityManager = null;
        $this->client = null;
        parent::tearDown();
    }
}
