<?php

namespace App\DataFixtures;

use App\Entity\Company;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class CompanyFixtures extends Fixture
{
  public function load(ObjectManager $manager): void
  {
    $faker = Factory::create('fr_FR');

    $companiesData = [
      [
        'name'      => $faker->text(35),
        'siret'     => $faker->text(14),
        'address'   => $faker->text(55),
      ],
      [
        'name'      => $faker->text(35),
        'siret'     => $faker->text(14),
        'address'   => $faker->text(55),
      ],
      [
        'name'      => $faker->text(35),
        'siret'     => $faker->text(14),
        'address'   => $faker->text(55),
      ],
      [
        'name'      => $faker->text(35),
        'siret'     => $faker->text(14),
        'address'   => $faker->text(55),
      ],
      [
        'name'      => $faker->text(35),
        'siret'     => $faker->text(14),
        'address'   => $faker->text(55),
      ],
    ];

    foreach ($companiesData as $k => $v) {
      $company = new Company();
      $company->setName($v['name']);
      $company->setSiret($v['siret']);
      $company->setAddress($v['address']);

      $manager->persist($company);
      $this->addReference('company-' . $k, $company);
    }
    $manager->flush();
  }
}
