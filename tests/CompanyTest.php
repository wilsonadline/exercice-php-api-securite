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

  // public function testAdminCanCreateCompany(): void
  // {
  //     $token = $this->authenticateAsAdmin();
  //     $client = static::createClient();

  //     $response = $client->request('POST', '/api/companies', [
  //         'headers' => [
  //             'Authorization' => 'Bearer ' . $token,
  //             'Content-Type' => 'application/ld+json',
  //         ],
  //         'json' => [
  //             'name' => 'Admin Company',
  //             'siret' => '12345678901234',
  //             'address' => 'Admin Street',
  //         ],
  //     ]);

  //     $this->assertResponseIsSuccessful();
  //     $this->assertJsonContains(['name' => 'Admin Company']);
  // }

  public function testConsultantCannotCreateCompany(): void
  {
    $token = $this->authenticateAsConsultant();
    $client = static::createClient();

    $response = $client->request('POST', '/api/companies', [
      'headers' => ['Authorization' => 'Bearer ' . $token],
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


  public function testConsultantCanViewCompanies(): void
  {
    $token = $this->authenticateAsConsultant();
    $client = static::createClient();

    $response = $client->request('GET', '/api/companies', [
      'headers' => ['Authorization' => 'Bearer ' . $token],
    ]);

    if ($response->getStatusCode() !== 200) {
      echo $response->getContent();
    }

    $this->assertResponseIsSuccessful();
  }


  public function testAdminCanCreateCompany(): void
  {
    $token = $this->authenticateAsAdmin();
    $client = static::createClient();

    $response = $client->request('POST', '/api/companies', [
      'headers' => [
        'Authorization' => 'Bearer ' . $token,
        'Content-Type' => 'application/ld+json',
      ],
      'json' => [
        'name' => 'Admin Company',
        'siret' => '12345678901234',
        'address' => 'Admin Street',
      ],
    ]);

    // En cas d'erreur, affiche le contenu pour savoir ce qui a échoué
    if ($response->getStatusCode() !== 201) {
      echo $response->getContent();
    }

    $this->assertResponseIsSuccessful();
    $this->assertJsonContains(['name' => 'Admin Company']);
  }

  public function testAdminCanDeleteCompany(): void
  {
    $token = $this->authenticateAsAdmin();
    $client = static::createClient();

    $client->request('DELETE', '/api/companies/2', [
      'headers' => ['Authorization' => 'Bearer ' . $token],
    ]);

    $this->assertResponseStatusCodeSame(204);
  }
}
