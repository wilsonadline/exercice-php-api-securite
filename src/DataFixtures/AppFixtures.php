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
        $adminUser->setPassword($this->passwordHasher->hashPassword($adminUser, 'admin_password'));
        $manager->persist($adminUser);
        $this->addReference('user-0', $adminUser);

        $managerUser = new User();
        $managerUser->setEmail('manager@local.host');
        $managerUser->setRoles(['ROLE_MANAGER']);
        $managerUser->setPassword($this->passwordHasher->hashPassword($managerUser, 'manager_password'));
        $manager->persist($managerUser);
        $this->addReference('user-1', $managerUser);

        $consultantUser = new User();
        $consultantUser->setEmail('consultant@local.host');
        $consultantUser->setRoles(['ROLE_CONSULTANT']);
        $consultantUser->setPassword($this->passwordHasher->hashPassword($consultantUser, 'consultant_password'));
        $manager->persist($consultantUser);
        $this->addReference('user-2', $consultantUser);

        $nonMemberUser = new User();
        $nonMemberUser->setEmail('nonmember@local.host');
        $nonMemberUser->setRoles(['ROLE_USER']);
        $nonMemberUser->setPassword($this->passwordHasher->hashPassword($nonMemberUser, 'nonmember_password'));
        $manager->persist($nonMemberUser);
        $this->addReference('user-3', $nonMemberUser);

        // Création des associations CompanyUserRole
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
