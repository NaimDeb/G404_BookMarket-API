<?php
namespace App\Tests\Functional\Controller;

use App\Entity\User;
use App\Entity\ProfessionalDetails;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;

class UserControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    public function testApiMeEndpointWithoutAuthentication()
    {
        $this->client->request('GET', '/api/me');
        
        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());
    }

    public function testApiMeEndpointWithRegularUser()
    {
        // Create regular user
        $user = new User();
        $user->setEmail('regular@test.com');
        $user->setPassword(password_hash('password123', PASSWORD_BCRYPT));
        $user->setRoles(['ROLE_USER']);
        
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // Get JWT token
        $this->client->request(
            'POST',
            '/api/login_check',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'username' => 'regular@test.com',
                'password' => 'password123'
            ])
        );

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $token = $data['token'];

        // Test /api/me endpoint
        $this->client->request(
            'GET',
            '/api/me',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
                'CONTENT_TYPE' => 'application/json'
            ]
        );

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('regular@test.com', $responseData['email']);
        $this->assertArrayNotHasKey('professionalDetails', $responseData);
    }

    public function testApiMeEndpointWithProUser()
    {
        // Create pro user with details
        $proUser = new User();
        $proUser->setEmail('pro@test.com');
        $proUser->setPassword(password_hash('password123', PASSWORD_BCRYPT));
        $proUser->setRoles(['ROLE_USER', 'ROLE_PRO']);

        $proDetails = new ProfessionalDetails();
        $proDetails->setCompanyName('Test Company');
        // $proDetails->setSiret('12345678901234');
        $proDetails->setUser($proUser);
        
        $this->entityManager->persist($proUser);
        $this->entityManager->persist($proDetails);
        $this->entityManager->flush();

        // Get JWT token
        $this->client->request(
            'POST',
            '/api/login_check',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'username' => 'pro@test.com',
                'password' => 'password123'
            ])
        );

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $token = $data['token'];

        // Test /api/me endpoint
        $this->client->request(
            'GET',
            '/api/me',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
                'CONTENT_TYPE' => 'application/json'
            ]
        );

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('pro@test.com', $responseData['email']);
        $this->assertArrayHasKey('professionalDetails', $responseData);
        $this->assertEquals('Test Company', $responseData['professionalDetails']['companyName']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        
        // Clean up database
        $this->entityManager->createQuery('DELETE FROM App\Entity\ProfessionalDetails')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\User')->execute();
        
        $this->entityManager->close();
        $this->entityManager = null;
    }
}