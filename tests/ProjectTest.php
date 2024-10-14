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
        'email' => 'consultant@local.host',
        'password' => 'consultant_password',
      ],
    ]);

    $this->assertResponseIsSuccessful();
    $data = $response->toArray();
    return $data['token'];
  }

  private function authenticateAsNonMember(): string
  {
    $client = static::createClient();
    $response = $client->request('POST', '/api/auth', [
      'json' => [
        'email' => 'nonmember@local.host',
        'password' => 'nonmember_password',
      ],
    ]);

    $this->assertResponseIsSuccessful();
    $data = $response->toArray();
    return $data['token'];
  }

  public function testAdminCanCreateProject(): void
  {
    $token = $this->authenticateAsAdmin();
    $client = static::createClient();

    $response = $client->request('POST', '/api/projects', [
      'headers' => [
        'Authorization' => 'Bearer ' . $token,
        'Content-Type' => 'application/ld+json',
      ],
      'json' => [
        'title' => 'Admin Project',
        'description' => 'Created by admin',
        'company' => '/api/companies/3',
        'createdBy' => '/api/users/1'
      ],
    ]);

    $this->assertResponseIsSuccessful();
    $this->assertJsonContains(['title' => 'Admin Project']);
  }

  public function testManagerCanUpdateProject(): void
  {
    $token = $this->authenticateAsManager();
    $client = static::createClient();

    $client->request('PATCH', '/api/projects/1', [
      'headers' => [
        'Authorization' => 'Bearer ' . $token,
        'Content-Type' => 'application/merge-patch+json',
      ],
      'json' => ['title' => 'Updated Project Title'],
    ]);

    $this->assertResponseIsSuccessful();
  }

  public function testConsultantCannotCreateProject(): void
  {
    $token = $this->authenticateAsConsultant();
    $client = static::createClient();

    $client->request('POST', '/api/projects', [
      'headers' => [
        'Authorization' => 'Bearer ' . $token,
        'Content-Type' => 'application/ld+json',
      ],
      'json' => [
        'title' => 'Unauthorized Project',
        'description' => 'Created by consultant',
        'company' => '/api/companies/3',
      ],
    ]);

    $this->assertResponseStatusCodeSame(403);
  }

  public function testConsultantCanViewProjects(): void
  {
    $token = $this->authenticateAsConsultant();
    $client = static::createClient();

    $client->request('GET', '/api/projects', [
      'headers' => ['Authorization' => 'Bearer ' . $token],
    ]);

    $this->assertResponseIsSuccessful();
  }

  public function testConsultantCannotCreateCompany(): void
  {
    $token = $this->authenticateAsConsultant();
    $client = static::createClient();

    $response = $client->request('POST', '/api/companies', [
      'headers' => [
        'Authorization' => 'Bearer ' . $token,
        'Content-Type' => 'application/ld+json',
      ],
      'json' => [
        'name' => 'Consultant Company',
        'siret' => '98765432101234',
        'address' => 'Consultant Street',
      ],
    ]);

    if ($response->getStatusCode() !== 403) {
      echo $response->getContent();
    }

    $this->assertResponseStatusCodeSame(403);
  }


  public function testAdminCanDeleteProject(): void
  {
    $token = $this->authenticateAsAdmin();
    $client = static::createClient();

    $client->request('DELETE', '/api/projects/1', [
      'headers' => ['Authorization' => 'Bearer ' . $token],
    ]);

    $this->assertResponseStatusCodeSame(204);
  }
}
