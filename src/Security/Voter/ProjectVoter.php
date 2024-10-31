<?php

namespace App\Security\Voter;

use App\Entity\Project;
use App\Entity\User;
use App\Entity\Company;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Bundle\SecurityBundle\Security;

class ProjectVoter extends Voter
{
    public const VIEW = 'VIEW_PROJECT';
    public const EDIT = 'EDIT_PROJECT';
    public const DELETE = 'DELETE_PROJECT';
    public const CREATE = 'CREATE_PROJECT';

    private Security $security;
    private EntityManagerInterface $entityManager;

    public function __construct(Security $security, EntityManagerInterface $entityManager)
    {
        $this->security = $security;
        $this->entityManager = $entityManager;
    }

    protected function supports(string $attribute, $subject): bool
    {
        // Accepte explicitement null pour VIEW_PROJECT (collection)
        if ($attribute === self::VIEW && $subject === null) {
            return true;
        }

        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE, self::CREATE])
            && ($subject instanceof Project || $subject === null);
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        // ADMIN peut tout faire
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        // Pour la collection (GET /api/projects)
        if ($subject === null) {
            if ($attribute === self::CREATE) {
                return in_array('ROLE_MANAGER', $user->getRoles());
            }
            return $attribute === self::VIEW;
        }

        // Pour la création de projet
        if ($attribute === self::CREATE) {
            if ($subject instanceof Project && $subject->getCompany() !== null) {
                $company = $subject->getCompany();
                foreach ($user->getCompanyUserRoles() as $userRole) {
                    if ($userRole->getCompany() === $company) {
                        return $userRole->getRole() === 'manager';
                    }
                }
            }
            // Si l'utilisateur est manager, on l'autorise à initier la création
            return in_array('ROLE_MANAGER', $user->getRoles());
        }

        if (!$subject instanceof Project) {
            return false;
        }

        // Pour les autres actions (VIEW, EDIT, DELETE)
        $company = $subject->getCompany();
        foreach ($user->getCompanyUserRoles() as $userRole) {
            if ($userRole->getCompany() === $company) {
                switch ($attribute) {
                    case self::VIEW:
                        return true; // Tout membre peut voir
                    case self::EDIT:
                    case self::DELETE:
                        return $userRole->getRole() === 'manager';
                }
            }
        }

        return false;
    }
}
