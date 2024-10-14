<?php

namespace App\DataFixtures;

use App\Entity\Project;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ProjectFixtures extends Fixture implements DependentFixtureInterface
{
  public function load(ObjectManager $manager): void
  {
    $faker = Factory::create('fr_FR');

    $projectsData = [
      [
        'title'         => $faker->text(35),
        'description'   => $faker->text(400),
        'company'       => $this->getReference('company-0'),
        'createdBy'     => $this->getReference('user-0'),
      ],
      [
        'title'         => $faker->text(35),
        'description'   => $faker->text(400),
        'company'       => $this->getReference('company-0'),
        'createdBy'     => $this->getReference('user-0'),
      ],
      [
        'title'         => $faker->text(35),
        'description'   => $faker->text(400),
        'company'       => $this->getReference('company-0'),
        'createdBy'     => $this->getReference('user-1'),
      ],
      [
        'title'         => $faker->text(35),
        'description'   => $faker->text(400),
        'company'       => $this->getReference('company-1'),
        'createdBy'     => $this->getReference('user-1'),
      ],
      [
        'title'         => $faker->text(35),
        'description'   => $faker->text(400),
        'company'       => $this->getReference('company-1'),
        'createdBy'     => $this->getReference('user-2'),
      ],
      [
        'title'         => $faker->text(35),
        'description'   => $faker->text(400),
        'company'       => $this->getReference('company-1'),
        'createdBy'     => $this->getReference('user-2'),
      ],
    ];

    foreach ($projectsData as $k => $v) {
      $project = new Project();
      $project->setTitle($v['title']);
      $project->setDescription($v['description']);
      $project->setCompany($v['company']);
      $project->setCreatedBy($v['createdBy']);

      $manager->persist($project);
      $this->addReference('project-' . $k, $project);
    }
    $manager->flush();
  }

  public function getDependencies(): array
  {
    return [
      CompanyFixtures::class,
      AppFixtures::class,
    ];
  }
}
