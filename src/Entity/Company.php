<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\State\CompanyCollectionProvider;
use App\Repository\CompanyRepository;

#[ORM\Entity(repositoryClass: CompanyRepository::class)]
#[ApiResource(
    operations: array(
        new GetCollection(
            normalizationContext: array('groups' => array('company:read')),
            security: "is_granted('IS_AUTHENTICATED_FULLY')",
            provider: CompanyCollectionProvider::class,
        ),
        new Get(
            normalizationContext: array('groups' => array('company:read', 'company:detail')),
            security: "is_granted('VIEW_COMPANY', object)",
            securityMessage: "Only users who belong to the company can view the company details."
        ),
        new Post(
            normalizationContext: array('groups' => array('company:read')),
            denormalizationContext: array('groups' => array('company:write')),
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: "Only admins can create companies."
        ),
        new Patch(
            normalizationContext: array('groups' => array('company:read')),
            denormalizationContext: array('groups' => array('company:update')),
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: "Only admins can update companies."
        ),
        new Delete(
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: "Only admins can delete companies."
        )
    )
)]
class Company
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    #[Groups(['company:read', 'user:detail', 'user:list'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom est obligatoire')]
    #[Groups(['company:read', 'company:write', 'company:update', 'user:detail', 'user:list'])]
    private ?string $name = null;

    #[ORM\Column(length: 14)]
    #[Assert\NotBlank(message: 'Le SIRET est obligatoire')]
    #[Assert\Length(exactly: 14, exactMessage: 'Le SIRET doit contenir exactement {{ limit }} caractères')]
    #[Groups(['company:read', 'company:write', 'company:update'])]
    private ?string $siret = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "L'adresse est obligatoire")]
    #[Groups(['company:read', 'company:write', 'company:update'])]
    private ?string $address = null;

    #[ORM\OneToMany(mappedBy: 'company', targetEntity: Project::class, cascade: ['remove'], orphanRemoval: true)]
    #[Groups(['company:detail'])]
    private Collection $projects;

    #[ORM\OneToMany(
        mappedBy: 'company',
        targetEntity: CompanyUserRole::class,
        cascade: ['remove'],
        orphanRemoval: true
    )]
    #[Groups(['company:detail'])]
    private Collection $companyUserRoles;

    public function __construct()
    {
        $this->projects = new ArrayCollection();
        $this->companyUserRoles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getSiret(): ?string
    {
        return $this->siret;
    }

    public function setSiret(string $siret): self
    {
        $this->siret = $siret;
        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;
        return $this;
    }

    /**
     * @return Collection<int, Project>
     */
    public function getProjects(): Collection
    {
        return $this->projects;
    }

    public function addProject(Project $project): self
    {
        if (!$this->projects->contains($project)) {
            $this->projects[] = $project;
            $project->setCompany($this);
        }
        return $this;
    }

    public function removeProject(Project $project): self
    {
        if ($this->projects->removeElement($project)) {
            if ($project->getCompany() === $this) {
                $project->setCompany(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, CompanyUserRole>
     */
    public function getCompanyUserRoles(): Collection
    {
        return $this->companyUserRoles;
    }

    public function addCompanyUserRole(CompanyUserRole $companyUserRole): self
    {
        if (!$this->companyUserRoles->contains($companyUserRole)) {
            $this->companyUserRoles[] = $companyUserRole;
            $companyUserRole->setCompany($this);
        }
        return $this;
    }

    public function removeCompanyUserRole(CompanyUserRole $companyUserRole): self
    {
        if ($this->companyUserRoles->removeElement($companyUserRole)) {
            if ($companyUserRole->getCompany() === $this) {
                $companyUserRole->setCompany(null);
            }
        }
        return $this;
    }
}
