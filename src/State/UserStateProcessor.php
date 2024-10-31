<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use App\Entity\CompanyUserRole;
use Symfony\Bundle\SecurityBundle\Security;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class UserStateProcessor implements ProcessorInterface
{
    public function __construct(
        private UserPasswordHasher $passwordHasher,
        private Security $security,
        private EntityManagerInterface $entityManager
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if ($data instanceof User) {
            /** @var User $currentUser */
            $currentUser = $this->security->getUser();

            // Si c'est une création d'utilisateur
            if (!$data->getId()) {
                foreach ($data->getCompanyUserRoles() as $companyUserRole) {
                    // Vérifie si l'utilisateur courant a les droits dans cette société
                    if (!$this->canManageInCompany($currentUser, $companyUserRole->getCompany())) {
                        throw new AccessDeniedHttpException('You cannot create users in this company');
                    }

                    // Si c'est un manager, il ne peut pas créer d'admin
                    if (
                        !$this->security->isGranted('ROLE_ADMIN')
                        && $companyUserRole->getRole() === 'admin'
                    ) {
                        throw new AccessDeniedHttpException('You cannot create admin users');
                    }
                }
            }
        }

        // Utilise le password hasher pour la suite du traitement
        return $this->passwordHasher->process($data, $operation, $uriVariables, $context);
    }

    private function canManageInCompany(User $user, $company): bool
    {
        // Admin peut tout faire
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        // Pour les managers, vérifie leur appartenance à la société
        foreach ($user->getCompanyUserRoles() as $role) {
            if ($role->getCompany() === $company) {
                return $role->getRole() === 'manager' || $role->getRole() === 'admin';
            }
        }

        return false;
    }
}
