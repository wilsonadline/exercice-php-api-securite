<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Project;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class ProjectCollectionProvider implements ProviderInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private Security $security
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array|null
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return [];
        }

        $qb = $this->entityManager->createQueryBuilder()
            ->select('p', 'c')
            ->from(Project::class, 'p')
            ->join('p.company', 'c')
            ->join('c.companyUserRoles', 'cur');

        // Si admin, voir tous les projets
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return $qb->getQuery()->getResult();
        }

        // Pour les autres, filtre par leurs sociétés et rôles
        $qb->andWhere('cur.user = :user')
            ->setParameter('user', $user);

        return $qb->getQuery()->getResult();
    }
}
