<?php

namespace App\Repository;

use App\Entity\Company;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Attribute\AsRepository;

#[AsRepository(Company::class)]
class CompanyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Company::class);
    }

    public function findCompaniesByUser(User $user): array
    {
        return $this->createQueryBuilder('c')
            ->join('c.companyUserRoles', 'cur')
            ->where('cur.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }
}
