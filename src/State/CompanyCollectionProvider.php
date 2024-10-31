<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Company;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

readonly class CompanyCollectionProvider implements ProviderInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private Security               $security
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return ['hydra:member' => []];
        }

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('c')
            ->from(Company::class, 'c');

        // Si c'est un admin, retourne toutes les sociétés
        if ($this->security->isGranted('ROLE_ADMIN')) {
            $results = $qb->getQuery()->getResult();
            return [
                'hydra:member' => $results,
                'hydra:totalItems' => count($results)
            ];
        }

        // Pour les autres utilisateurs, filtre par leurs sociétés
        $results = $qb->join('c.companyUserRoles', 'cur')
            ->where('cur.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();

        return [
            'hydra:member' => $results,
            'hydra:totalItems' => count($results)
        ];
    }
}
