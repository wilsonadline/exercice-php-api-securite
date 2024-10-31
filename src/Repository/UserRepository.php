<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;

class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findUsersByCompanyIds(array $companyIds)
    {
        return $this->createQueryBuilder('u')
            ->innerJoin('u.companyUserRoles', 'cur')
            ->where('cur.company IN (:companyIds)')
            ->setParameter('companyIds', $companyIds)
            ->getQuery()
            ->getResult();
    }
}
