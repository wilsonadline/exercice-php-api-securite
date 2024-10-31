<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProjectItemProvider implements ProviderInterface
{
  public function __construct(
    private EntityManagerInterface $entityManager
  ) {}

  public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?Project
  {
    $id = $uriVariables['id'] ?? null;
    if (!$id) {
      throw new NotFoundHttpException('Project ID is required');
    }

    $project = $this->entityManager->getRepository(Project::class)->find($id);
    if (!$project) {
      throw new NotFoundHttpException('Project not found');
    }

    return $project;
  }
}
