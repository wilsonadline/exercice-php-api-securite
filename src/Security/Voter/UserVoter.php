<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Entity\CompanyUserRole;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Bundle\SecurityBundle\Security;

class UserVoter extends Voter
{
    public const VIEW = 'VIEW';
    public const EDIT = 'EDIT';
    public const DELETE = 'DELETE';

    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        // Pour la collection, on supporte VIEW
        if ($attribute === self::VIEW && $subject === null) {
            return true;
        }

        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE])
            && ($subject instanceof User || $subject === null);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $currentUser = $token->getUser();
        if (!$currentUser instanceof User) {
            return false;
        }

        // Admin peut tout faire
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        // Si c'est la collection (GET /api/users)
        if ($subject === null && $attribute === self::VIEW) {
            return $this->security->isGranted('ROLE_MANAGER');
        }

        // Si on arrive ici, $subject doit être un User
        if (!$subject instanceof User) {
            return false;
        }

        // Manager
        if ($this->security->isGranted('ROLE_MANAGER')) {
            return $this->canManagerAccess($currentUser, $subject, $attribute);
        }

        // Simple utilisateur (dont consultant)
        return $this->canUserAccess($currentUser, $subject, $attribute);
    }

    private function canManagerAccess(User $manager, User $subject, string $attribute): bool
    {
        // Le manager peut toujours voir et modifier son propre profil
        if ($manager === $subject) {
            return in_array($attribute, [self::VIEW, self::EDIT]);
        }

        // Vérifie si le manager et l'utilisateur partagent une société
        $managerCompanies = $this->getUserCompanies($manager);
        $subjectCompanies = $this->getUserCompanies($subject);
        $sharedCompanies = array_intersect($managerCompanies, $subjectCompanies);

        if (!empty($sharedCompanies)) {
            // Le manager ne peut pas gérer les admins
            if (in_array('ROLE_ADMIN', $subject->getRoles())) {
                return $attribute === self::VIEW;
            }

            return match ($attribute) {
                self::VIEW, self::EDIT => true,
                default => false
            };
        }

        return false;
    }

    private function canUserAccess(User $currentUser, User $subject, string $attribute): bool
    {
        // Un utilisateur peut uniquement voir et modifier son propre profil
        if ($currentUser === $subject) {
            return in_array($attribute, [self::VIEW, self::EDIT]);
        }

        return false;
    }

    private function getUserCompanies(User $user): array
    {
        return array_map(
            function (CompanyUserRole $role) {
                return $role->getCompany()->getId();
            },
            $user->getCompanyUserRoles()->toArray()
        );
    }
}
