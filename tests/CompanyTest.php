<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;

class CompanyTest extends ApiTestCase
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

    // Tests for Admin
    public function testAdminCanSeeAllCompanies(): void
    {
        $token = $this->authenticateAsAdmin();
        $client = static::createClient();
        $response = $client->request('GET', '/api/companies', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/ld+json'
            ],
        ]);
        $this->assertResponseIsSuccessful();
    }

    public function testAdminCanCreateCompany(): void
    {
        $token = $this->authenticateAsAdmin();
        $client = static::createClient();
        $response = $client->request('POST', '/api/companies', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/ld+json'
            ],
            'json' => [
                'name' => 'New Company',
                'siret' => '12345678910112',
                'address' => 'new company street'
            ],
        ]);
        $this->assertResponseIsSuccessful();
    }

    public function testAdminCanViewCompany(): void
    {
        $token = $this->authenticateAsAdmin();
        $client = static::createClient();
        $response = $client->request('GET', '/api/companies/3', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/ld+json'
            ],
        ]);
        $this->assertResponseIsSuccessful();
    }

    public function testAdminCanUpdateCompany(): void
    {
        $token = $this->authenticateAsAdmin();
        $client = static::createClient();
        $response = $client->request('PATCH', '/api/companies/3', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/merge-patch+json'
            ],
            'json' => [
                'name' => 'Updated Company',
                'siret' => '12345678910112',
                'address' => 'new company street Updated'
            ],
        ]);
        $this->assertResponseIsSuccessful();
    }

    public function testAdminCanDeleteCompany(): void
    {
        $token = $this->authenticateAsAdmin();
        $client = static::createClient();
        $response = $client->request('DELETE', '/api/companies/3', [
            'headers' => ['Authorization' => 'Bearer ' . $token],
        ]);
        $this->assertResponseStatusCodeSame(204);
    }

    // Tests for Manager
    public function testManagerCanSeeOwnCompanies(): void
    {
        $token = $this->authenticateAsManager();
        $client = static::createClient();
        $response = $client->request('GET', '/api/companies', [
            'headers' => ['Authorization' => 'Bearer ' . $token],
        ]);
        $this->assertResponseIsSuccessful();
    }

    public function testManagerCannotCreateCompany(): void
    {
        $token = $this->authenticateAsManager();
        $client = static::createClient();
        $response = $client->request('POST', '/api/companies', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/ld+json'
            ],
            'json' => [
                'name' => 'Manager Company',
                'siret' => '23456789101123',
                'address' => 'new Manager street'
            ],
        ]);
        $this->assertResponseStatusCodeSame(403);
    }

    public function testManagerCanViewCompany(): void
    {
        $token = $this->authenticateAsManager();
        $client = static::createClient();
        $response = $client->request('GET', '/api/companies/1', [
            'headers' => ['Authorization' => 'Bearer ' . $token],
        ]);
        $this->assertResponseIsSuccessful();
    }

    public function testManagerCannotUpdateCompany(): void
    {
        $token = $this->authenticateAsManager();
        $client = static::createClient();
        $response = $client->request('PATCH', '/api/companies/1', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/merge-patch+json'
            ],
            'json' => [
                'name' => 'Updated Manager Company',
                'siret' => '34567891011234',
                'address' => 'new Manager street Updated'
            ],
        ]);
        $this->assertResponseStatusCodeSame(403);
    }

    public function testManagerCannotDeleteCompany(): void
    {
        $token = $this->authenticateAsManager();
        $client = static::createClient();
        $response = $client->request('DELETE', '/api/companies/1', [
            'headers' => ['Authorization' => 'Bearer ' . $token],
        ]);
        $this->assertResponseStatusCodeSame(403);
    }

    // Tests for Consultant
    public function testConsultantCannotSeeAllCompanies(): void
    {
        $token = $this->authenticateAsConsultant();
        $client = static::createClient();
        $response = $client->request('GET', '/api/companies', [
            'headers' => ['Authorization' => 'Bearer ' . $token],
        ]);
        $this->assertResponseIsSuccessful(403);
    }

    public function testConsultantCannotCreateCompany(): void
    {
        $token = $this->authenticateAsConsultant();
        $client = static::createClient();
        $response = $client->request('POST', '/api/companies', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/ld+json'
            ],
            'json' => [
                'name' => 'Consultant Company',
                'siret' => '45678910112345',
                'address' => 'new Consultant street Updated'
            ],
        ]);
        $this->assertResponseStatusCodeSame(403);
    }

    public function testConsultantCanSeeOwnCompany(): void
    {
        $token = $this->authenticateAsConsultant();
        $client = static::createClient();
        $response = $client->request('GET', '/api/companies/2', [
            'headers' => ['Authorization' => 'Bearer ' . $token],
        ]);
        $this->assertResponseIsSuccessful();
    }

    public function testConsultantCannotUpdateCompany(): void
    {
        $token = $this->authenticateAsConsultant();
        $client = static::createClient();
        $response = $client->request('PATCH', '/api/companies/2', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/merge-patch+json'
            ],
            'json' => [
                'name' => 'Updated Consultant Company',
                'siret' => '45678910112345',
                'address' => 'new Consultant street Updated'
            ],
        ]);
        $this->assertResponseStatusCodeSame(403);
    }

    public function testConsultantCannotDeleteCompany(): void
    {
        $token = $this->authenticateAsConsultant();
        $client = static::createClient();
        $response = $client->request('DELETE', '/api/companies/2', [
            'headers' => ['Authorization' => 'Bearer ' . $token],
        ]);
        $this->assertResponseStatusCodeSame(403);
    }
}
