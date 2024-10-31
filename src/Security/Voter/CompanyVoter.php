<?php

namespace App\Security\Voter;

use App\Entity\Company;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Bundle\SecurityBundle\Security;

class CompanyVoter extends Voter
{
    public const VIEW = 'VIEW_COMPANY';
    public const EDIT = 'EDIT_COMPANY';
    public const CREATE = 'CREATE_COMPANY';
    public const DELETE = 'DELETE_COMPANY';

    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject): bool
    {
        // Gére le cas de la collection (comme pour GET /api/companies)
        if ($attribute === self::VIEW && $subject === null) {
            return true;
        }

        return in_array($attribute, [self::VIEW, self::EDIT, self::CREATE, self::DELETE])
            && ($subject instanceof Company || $subject === null);
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        // L'admin a tous les droits
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        // Pour une collection, autorise la vue (le filtrage se fait dans le provider)
        if ($subject === null) {
            return $attribute === self::VIEW;
        }

        // Création et suppression réservées à l'admin
        if (in_array($attribute, [self::CREATE, self::DELETE])) {
            return false;
        }

        // Pour une company spécifique, vérifie le rôle de l'utilisateur dans cette company
        /** @var Company $company */
        $company = $subject;

        foreach ($user->getCompanyUserRoles() as $companyUserRole) {
            if ($companyUserRole->getCompany() === $company) {
                switch ($attribute) {
                    case self::VIEW:
                        // Tout membre de la société peut la voir
                        return true;
                    case self::EDIT:
                        // Seuls admin et manager de la société peuvent modifier
                        return in_array($companyUserRole->getRole(), ['admin', 'manager']);
                }
            }
        }

        return false;
    }
}
