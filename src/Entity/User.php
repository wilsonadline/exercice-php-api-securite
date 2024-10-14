<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: "Only admins can view the list of users."
        ),
        new Post(
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: "Only admins can create users."
        ),
        new Get(
            security: "is_granted('ROLE_ADMIN') or object == user",
            securityMessage: "Only admins or the user themselves can view their details."
        ),
        new Patch(
            security: "is_granted('ROLE_ADMIN') or object == user",
            securityMessage: "Only admins or the user themselves can modify their details."
        ),
        new Delete(
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: "Only admins can delete users."
        )
    ]
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    private ?string $plainPassword = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: CompanyUserRole::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $companyUserRoles;

    #[ORM\OneToMany(mappedBy: 'createdBy', targetEntity: Project::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $projects;

    public function __construct()
    {
        $this->companyUserRoles = new ArrayCollection();
        $this->projects = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getRoles(): array
    {
        return array_unique($this->roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
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
            $this->companyUserRoles->add($companyUserRole);
            $companyUserRole->setUser($this);
        }

        return $this;
    }

    public function removeCompanyUserRole(CompanyUserRole $companyUserRole): self
    {
        if ($this->companyUserRoles->removeElement($companyUserRole)) {
            if ($companyUserRole->getUser() === $this) {
                $companyUserRole->setUser(null);
            }
        }

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
            $this->projects->add($project);
            $project->setCreatedBy($this);
        }

        return $this;
    }

    public function removeProject(Project $project): self
    {
        if ($this->projects->removeElement($project)) {
            if ($project->getCreatedBy() === $this) {
                $project->setCreatedBy(null);
            }
        }

        return $this;
    }
}
