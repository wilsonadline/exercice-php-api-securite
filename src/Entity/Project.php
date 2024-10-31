<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use App\State\ProjectCollectionProvider;
use App\State\ProjectItemProvider;
use App\State\ProjectStateProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(
            provider: ProjectCollectionProvider::class,
            security: "is_granted('VIEW_PROJECT', null)",
            normalizationContext: ['groups' => ['project:read']]
        ),
        new Get(
            provider: ProjectItemProvider::class,
            security: "is_granted('VIEW_PROJECT', object)",
            normalizationContext: ['groups' => ['project:read', 'project:detail', 'company:read', 'user:read']]
        ),
        new Post(
            processor: ProjectStateProcessor::class,
            security: "is_granted('CREATE_PROJECT', object)",
            normalizationContext: ['groups' => ['project:read']],
            denormalizationContext: ['groups' => ['project:write']]
        ),
        new Patch(
            provider: ProjectItemProvider::class,
            processor: ProjectStateProcessor::class,
            security: "is_granted('EDIT_PROJECT', object)",
            normalizationContext: ['groups' => ['project:read']],
            denormalizationContext: ['groups' => ['project:update']]
        ),
        new Delete(
            provider: ProjectItemProvider::class,
            processor: ProjectStateProcessor::class,
            security: "is_granted('DELETE_PROJECT', object)"
        )
    ],
    normalizationContext: ['groups' => ['project:read']]
)]
class Project
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['project:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['project:read', 'project:write', 'project:update'])]
    private ?string $title = null;

    #[ORM\Column(type: 'text')]
    #[Groups(['project:read', 'project:write', 'project:update'])]
    private ?string $description = null;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['project:read'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\ManyToOne(targetEntity: Company::class, inversedBy: 'projects')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['project:read', 'project:write', 'project:detail'])]
    private ?Company $company = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['project:read', 'project:detail'])]
    private ?User $createdBy = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): self
    {
        $this->company = $company;
        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): self
    {
        $this->createdBy = $createdBy;
        return $this;
    }
}
