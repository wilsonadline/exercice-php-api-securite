<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\CompanyUserRole;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture implements DependentFixtureInterface
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Récupération des entreprises créées dans CompanyFixtures
        $company1 = $this->getReference('company-0');
        $company2 = $this->getReference('company-1');

        // Création des utilisateurs
        $adminUser = new User();
        $adminUser->setEmail('admin@local.host');
        $adminUser->setRoles(['ROLE_ADMIN']);
        $hashedPassword = $this->passwordHasher->hashPassword($adminUser, 'admin_password');
        $adminUser->setPassword($hashedPassword);
        $manager->persist($adminUser);
        $this->addReference('user-0', $adminUser);


        // Créer un utilisateur manager+.
        $managerUser = new User();
        $managerUser->setEmail('manager@local.host');
        $managerUser->setRoles(['ROLE_MANAGER']);
        $hashedPassword = $this->passwordHasher->hashPassword($managerUser, 'manager_password');
        $managerUser->setPassword($hashedPassword);
        $manager->persist($managerUser);
        $this->addReference('user-1', $managerUser);


        // Créer un utilisateur standard
        $consultantUser = new User();
        $consultantUser->setEmail('user@local.host');
        $consultantUser->setRoles(['ROLE_CONSULTANT']);
        $hashedPassword = $this->passwordHasher->hashPassword($consultantUser, 'user_password');
        $consultantUser->setPassword($hashedPassword);
        $manager->persist($consultantUser);
        $this->addReference('user-2', $consultantUser);

        $consultantUser1 = new User();
        $consultantUser1->setEmail('user1@local.host');
        $consultantUser1->setRoles(['ROLE_CONSULTANT']);
        $hashedPassword = $this->passwordHasher->hashPassword($consultantUser1, 'user1_password');
        $consultantUser1->setPassword($hashedPassword);
        $manager->persist($consultantUser1);
        $this->addReference('user-3', $consultantUser1);

        $consultantUser2 = new User();
        $consultantUser2->setEmail('user2@local.host');
        $consultantUser2->setRoles(['ROLE_CONSULTANT']);
        $hashedPassword = $this->passwordHasher->hashPassword($consultantUser2, 'user2_password');
        $consultantUser2->setPassword($hashedPassword);
        $manager->persist($consultantUser2);
        $this->addReference('user-4', $consultantUser2);

        $consultantUser3 = new User();
        $consultantUser3->setEmail('user3@local.host');
        $consultantUser3->setRoles(['ROLE_CONSULTANT']);
        $hashedPassword = $this->passwordHasher->hashPassword($consultantUser3, 'user3_password');
        $consultantUser3->setPassword($hashedPassword);
        $manager->persist($consultantUser3);
        $this->addReference('user-5', $consultantUser3);

        $consultantUser4 = new User();
        $consultantUser4->setEmail('user4@local.host');
        $consultantUser4->setRoles(['ROLE_CONSULTANT']);
        $hashedPassword = $this->passwordHasher->hashPassword($consultantUser4, 'user4_password');
        $consultantUser4->setPassword($hashedPassword);
        $manager->persist($consultantUser4);
        $this->addReference('user-6', $consultantUser4);


        // Création des rôles liés aux utilisateurs
        $adminUserCompanyRole = new CompanyUserRole();
        $adminUserCompanyRole->setUser($adminUser);
        $adminUserCompanyRole->setCompany($company1);
        $adminUserCompanyRole->setRole('admin');
        $manager->persist($adminUserCompanyRole);

        $managerUserCompanyRole = new CompanyUserRole();
        $managerUserCompanyRole->setUser($managerUser);
        $managerUserCompanyRole->setCompany($company1);
        $managerUserCompanyRole->setRole('manager');
        $manager->persist($managerUserCompanyRole);

        $consultantUserCompanyRole = new CompanyUserRole();
        $consultantUserCompanyRole->setUser($consultantUser);
        $consultantUserCompanyRole->setCompany($company2);
        $consultantUserCompanyRole->setRole('consultant');
        $manager->persist($consultantUserCompanyRole);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CompanyFixtures::class,
        ];
    }
}
