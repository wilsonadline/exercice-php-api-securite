<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;

class ProjectTest extends ApiTestCase
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
    public function testAdminCanSeeAllProjects(): void
    {
        $token = $this->authenticateAsAdmin();
        $client = static::createClient();
        $response = $client->request('GET', '/api/projects', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/ld+json'
            ],
        ]);
        $this->assertResponseIsSuccessful();
    }

    public function testAdminCanCreateProject(): void
    {
        $token = $this->authenticateAsAdmin();
        $client = static::createClient();
        $response = $client->request('POST', '/api/projects', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/ld+json'
            ],
            'json' => [
                'title' => 'New Project',
                'description' => 'Project Description',
                'company' => '/api/companies/1'
            ],
        ]);
        $this->assertResponseIsSuccessful();
    }

    public function testAdminCanViewProject(): void
    {
        $token = $this->authenticateAsAdmin();
        $client = static::createClient();
        $response = $client->request('GET', '/api/projects/1', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/ld+json'
            ],
        ]);
        $this->assertResponseIsSuccessful();
    }

    public function testAdminCanUpdateProject(): void
    {
        $token = $this->authenticateAsAdmin();
        $client = static::createClient();
        $response = $client->request('PATCH', '/api/projects/1', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/merge-patch+json'
            ],
            'json' => [
                'title' => 'Updated Project',
                'description' => 'Updated Project Description'
            ],
        ]);
        $this->assertResponseIsSuccessful();
    }

    public function testAdminCanDeleteProject(): void
    {
        $token = $this->authenticateAsAdmin();
        $client = static::createClient();
        $response = $client->request('DELETE', '/api/projects/1', [
            'headers' => ['Authorization' => 'Bearer ' . $token],
        ]);
        $this->assertResponseStatusCodeSame(204);
    }

    // Tests for Manager
    public function testManagerCanSeeOwnProjects(): void
    {
        $token = $this->authenticateAsManager();
        $client = static::createClient();
        $response = $client->request('GET', '/api/projects', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/ld+json'
            ],
        ]);
        $this->assertResponseIsSuccessful();
    }

    public function testManagerCanCreateProject(): void
    {
        $token = $this->authenticateAsManager();
        $client = static::createClient();
        $response = $client->request('POST', '/api/projects', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/ld+json'
            ],
            'json' => [
                'title' => 'Manager Project',
                'description' => 'Manager Project Description',
                'company' => '/api/companies/1'
            ],
        ]);
        $this->assertResponseIsSuccessful();
    }

    public function testManagerCanViewProject(): void
    {
        $token = $this->authenticateAsManager();
        $client = static::createClient();
        $response = $client->request('GET', '/api/projects/2', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/ld+json'
            ],
        ]);
        $this->assertResponseIsSuccessful();
    }

    public function testManagerCanUpdateProject(): void
    {
        $token = $this->authenticateAsManager();
        $client = static::createClient();
        $response = $client->request('PATCH', '/api/projects/2', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/merge-patch+json'
            ],
            'json' => [
                'title' => 'Updated Manager Project',
                'description' => 'Updated Manager Project Description'
            ],
        ]);
        $this->assertResponseIsSuccessful();
    }

    public function testManagerCanDeleteProject(): void
    {
        $token = $this->authenticateAsManager();
        $client = static::createClient();
        $response = $client->request('DELETE', '/api/projects/5', [
            'headers' => ['Authorization' => 'Bearer ' . $token],
        ]);
        $this->assertResponseStatusCodeSame(204);
    }

    // Tests for Consultant
    public function testConsultantCanSeeOnlyOwnCompanyProjects(): void
    {
        $token = $this->authenticateAsConsultant();
        $client = static::createClient();
        $response = $client->request('GET', '/api/projects', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/ld+json'
            ],
        ]);

        $this->assertResponseIsSuccessful();

        // Vérifie que seuls les projets de la société du consultant sont retournés
        $data = $response->toArray();
        $this->assertArrayHasKey('member', $data);

        // Vérifie que tous les projets retournés appartiennent à la company 2 (celle du consultant)
        foreach ($data['member'] as $project) {
            $this->assertStringEndsWith('/companies/2', $project['company']);
        }
    }

    public function testConsultantCanSeeOwnProject(): void
    {
        $token = $this->authenticateAsConsultant();
        $client = static::createClient();
        $response = $client->request('GET', '/api/projects/3', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/ld+json'
            ],
        ]);
        $this->assertResponseIsSuccessful();
    }

    public function testConsultantCannotUpdateProject(): void
    {
        $token = $this->authenticateAsConsultant();
        $client = static::createClient();
        $response = $client->request('PATCH', '/api/projects/3', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/merge-patch+json'
            ],
            'json' => [
                'title' => 'Updated Consultant Project',
                'description' => 'Updated Consultant Project Description'
            ],
        ]);
        $this->assertResponseStatusCodeSame(403);
    }

    public function testConsultantCannotDeleteProject(): void
    {
        $token = $this->authenticateAsConsultant();
        $client = static::createClient();
        $response = $client->request('DELETE', '/api/projects/3', [
            'headers' => ['Authorization' => 'Bearer ' . $token],
        ]);
        $this->assertResponseStatusCodeSame(403);
    }
}
