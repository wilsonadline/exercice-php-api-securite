<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;

class UserTest extends ApiTestCase
{
    private function authenticateAsAdmin(): string
    {
        $client = static::createClient();
        $response = $client->request('POST', '/api/auth', [
            'json' => [
                'email' => 'admin@local.host',
                'password' => 'admin_password',
            ],
        ]);
        $this->assertResponseIsSuccessful();
        $data = $response->toArray();
        return $data['token'];
    }

    private function authenticateAsManager(): string
    {
        $client = static::createClient();
        $response = $client->request('POST', '/api/auth', [
            'json' => [
                'email' => 'manager@local.host',
                'password' => 'manager_password',
            ],
        ]);
        $this->assertResponseIsSuccessful();
        $data = $response->toArray();
        return $data['token'];
    }

    private function authenticateAsUpdatedManager(): string
    {
        $client = static::createClient();
        $response = $client->request('POST', '/api/auth', [
            'json' => [
                'email' => 'updatedmanager@local.host',
                'password' => 'manager_password',
            ],
        ]);
        $this->assertResponseIsSuccessful();
        $data = $response->toArray();
        return $data['token'];
    }

    private function authenticateAsConsultant(): string
    {
        $client = static::createClient();
        $response = $client->request('POST', '/api/auth', [
            'json' => [
                'email' => 'user@local.host',
                'password' => 'user_password',
            ],
        ]);
        $this->assertResponseIsSuccessful();
        $data = $response->toArray();
        return $data['token'];
    }

    private function authenticateAsConsultantUpdated(): string
    {
        $client = static::createClient();
        $response = $client->request('POST', '/api/auth', [
            'json' => [
                'email' => 'updatedconsultant@local.host',
                'password' => 'user_password',
            ],
        ]);
        $this->assertResponseIsSuccessful();
        $data = $response->toArray();
        return $data['token'];
    }

    // Tests for Admin
    public function testAdminCanSeeAllUsers(): void
    {
        $token = $this->authenticateAsAdmin();
        $client = static::createClient();
        $response = $client->request('GET', '/api/users', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/ld+json',
            ],
        ]);
        $this->assertResponseIsSuccessful();
    }

    public function testAdminCanCreateUser(): void
    {
        $token = $this->authenticateAsAdmin();
        $client = static::createClient();
        $response = $client->request('POST', '/api/users', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/ld+json',
            ],
            'json' => [
                'email' => 'newadmin@local.host',
                'plainPassword' => 'new_password',
                'roles' => ['ROLE_ADMIN'],
            ],
        ]);
        $this->assertResponseIsSuccessful();
    }

    public function testAdminCanViewUserProfile(): void
    {
        $token = $this->authenticateAsAdmin();
        $client = static::createClient();
        $response = $client->request('GET', '/api/users/1', [
            'headers' => ['Authorization' => 'Bearer ' . $token, 'Content-Type' => 'application/ld+json',],
        ]);
        $this->assertResponseIsSuccessful();
    }

    public function testAdminCanUpdateUserProfile(): void
    {
        $token = $this->authenticateAsAdmin();
        $client = static::createClient();
        $response = $client->request('PATCH', '/api/users/4', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/merge-patch+json',
            ],
            'json' => [
                'email' => 'updatedadmin@local.host',
            ],
        ]);
        $this->assertResponseIsSuccessful();
    }

    public function testAdminCanDeleteUser(): void
    {
        $token = $this->authenticateAsAdmin();
        $client = static::createClient();
        $response = $client->request('DELETE', '/api/users/4', [
            'headers' => ['Authorization' => 'Bearer ' . $token, 'Content-Type' => 'application/ld+json',],
        ]);
        $this->assertResponseStatusCodeSame(204);
    }

    // Tests for Manager
    public function testManagerCanSeeOwnUsers(): void
    {
        $token = $this->authenticateAsManager();
        $client = static::createClient();
        $response = $client->request('GET', '/api/users', [
            'headers' => ['Authorization' => 'Bearer ' . $token, 'Content-Type' => 'application/ld+json',],
        ]);
        $this->assertResponseIsSuccessful();
    }

    public function testManagerCanCreateUserInCompany(): void
    {
        $token = $this->authenticateAsManager();
        $client = static::createClient();
        $response = $client->request('POST', '/api/users', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/ld+json',
            ],
            'json' => [
                'email' => 'newmanager@local.host',
                'plainPassword' => 'new_password',
                'roles' => ['ROLE_MANAGER'],
            ],
        ]);
        $this->assertResponseIsSuccessful();
    }

    public function testManagerCanViewOwnUserProfile(): void
    {
        $token = $this->authenticateAsManager();
        $client = static::createClient();
        $response = $client->request('GET', '/api/users/2', [
            'headers' => ['Authorization' => 'Bearer ' . $token, 'Content-Type' => 'application/ld+json',],
        ]);
        $this->assertResponseIsSuccessful();
    }

    public function testManagerCanUpdateOwnUserProfile(): void
    {
        $token = $this->authenticateAsManager();
        $client = static::createClient();
        $response = $client->request('PATCH', '/api/users/2', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/merge-patch+json',
            ],
            'json' => [
                'email' => 'updatedmanager@local.host',
            ],
        ]);
        $this->assertResponseIsSuccessful();
    }

    public function testManagerCannotDeleteUser(): void
    {
        $token = $this->authenticateAsUpdatedManager();
        $client = static::createClient();
        $response = $client->request('DELETE', '/api/users/6', [
            'headers' => ['Authorization' => 'Bearer ' . $token, 'Content-Type' => 'application/ld+json',],
        ]);
        $this->assertResponseStatusCodeSame(403);
    }

    // Tests for Consultant
    public function testConsultantCannotSeeAllUsers(): void
    {
        $token = $this->authenticateAsConsultant();
        $client = static::createClient();
        $response = $client->request('GET', '/api/users', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/ld+json',
            ],
        ]);
        $this->assertResponseStatusCodeSame(403);
    }

    public function testConsultantCannotCreateUser(): void
    {
        $token = $this->authenticateAsConsultant();
        $client = static::createClient();
        $response = $client->request('POST', '/api/users', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/ld+json',
            ],
            'json' => [
                'email' => 'newconsultant@local.host',
                'plainPassword' => 'new_password',
                'roles' => ['ROLE_CONSULTANT'],
            ],
        ]);
        $this->assertResponseStatusCodeSame(403);
    }

    public function testConsultantCanSeeOwnProfile(): void
    {
        $token = $this->authenticateAsConsultant();
        $client = static::createClient();
        $response = $client->request('GET', '/api/users/3', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/ld+json',
            ],
        ]);
        $this->assertResponseIsSuccessful();
    }

    public function testConsultantCanUpdateOwnProfile(): void
    {
        $token = $this->authenticateAsConsultant();
        $client = static::createClient();
        $response = $client->request('PATCH', '/api/users/3', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/merge-patch+json',
            ],
            'json' => [
                'email' => 'updatedconsultant@local.host',
            ],
        ]);
        $this->assertResponseIsSuccessful();
    }

    public function testConsultantCannotDeleteUser(): void
    {
        $token = $this->authenticateAsConsultantUpdated();
        $client = static::createClient();
        $response = $client->request('DELETE', '/api/users/5', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/ld+json',
            ],
        ]);
        $this->assertResponseStatusCodeSame(403);
    }
}
