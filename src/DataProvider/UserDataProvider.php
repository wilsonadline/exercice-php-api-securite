<?php

namespace App\DataProvider;

use Symfony\Bundle\SecurityBundle\Security;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class UserDataProvider implements ProviderInterface
{
    private Security $security;
    private EntityManagerInterface $entityManager;

    public function __construct(Security $security, EntityManagerInterface $entityManager)
    {
        $this->security = $security;
        $this->entityManager = $entityManager;
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $currentUser = $this->security->getUser();

        if (!$currentUser instanceof User) {
            return [];
        }

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('u')
            ->from(User::class, 'u')
            ->leftJoin('u.companyUserRoles', 'cur')
            ->leftJoin('cur.company', 'c');

        // Si l'utilisateur est admin, retourne tous les utilisateurs
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return $qb->getQuery()->getResult();
        }

        // Si l'utilisateur est manager, filtre par sa société
        if ($this->security->isGranted('ROLE_MANAGER')) {
            // Récupére les IDs des sociétés du manager connecté
            $managerCompanyIds = [];
            foreach ($currentUser->getCompanyUserRoles() as $role) {
                $managerCompanyIds[] = $role->getCompany()->getId();
            }

            $qb->andWhere('c.id IN (:companyIds)')
                ->setParameter('companyIds', $managerCompanyIds);

            return $qb->getQuery()->getResult();
        }

        return [];
    }
}
