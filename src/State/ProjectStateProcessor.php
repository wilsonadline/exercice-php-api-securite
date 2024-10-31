<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\DeleteOperationInterface;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Project;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ProjectStateProcessor implements ProcessorInterface
{
    public function __construct(
        private ProcessorInterface $persistProcessor,
        private Security $security,
        private EntityManagerInterface $entityManager
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if (!$data instanceof Project) {
            return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
        }

        /** @var User $user */
        $user = $this->security->getUser();

        // Si c'est une opération de suppression
        if ($operation instanceof DeleteOperationInterface) {
            $this->entityManager->remove($data);
            $this->entityManager->flush();
            return null;
        }

        // Pour les autres opérations (création/modification)
        // Vérifie que l'utilisateur peut créer/modifier dans cette société
        if (!$this->canUserManageInCompany($user, $data->getCompany())) {
            throw new AccessDeniedHttpException('You cannot create/modify projects in this company');
        }

        // Pour une nouvelle création
        if (!$data->getId()) {
            $data->setCreatedAt(new \DateTime());
            $data->setCreatedBy($user);
        }
        // Pour une mise à jour
        else {
            $existingProject = $this->entityManager->getRepository(Project::class)->find($data->getId());
            if ($existingProject) {
                $data->setCreatedBy($existingProject->getCreatedBy());
                $data->setCreatedAt($existingProject->getCreatedAt());
            }
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }

    private function canUserManageInCompany(User $user, $company): bool
    {
        // ADMIN peut tout faire
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return true;
        }

        // Pour les autres, vérifie leur rôle dans la société
        foreach ($user->getCompanyUserRoles() as $role) {
            if ($role->getCompany() === $company) {
                return in_array($role->getRole(), ['manager']);
            }
        }

        return false;
    }
}
