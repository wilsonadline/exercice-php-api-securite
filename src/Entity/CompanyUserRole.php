<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity]
#[UniqueEntity(
    fields: ['user', 'company'],
    message: 'Cet utilisateur a déjà un rôle dans cette entreprise.'
)]
class CompanyUserRole
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    #[Groups(['user:detail', 'user:list', 'company:detail'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'companyUserRoles')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "L'utilisateur est obligatoire")]
    #[Groups(['company:detail', 'company_user_role:write'])]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Company::class, inversedBy: 'companyUserRoles')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "L'entreprise est obligatoire")]
    #[Groups(['user:detail', 'user:list', 'company_user_role:write'])]
    private ?Company $company = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: 'Le rôle est obligatoire')]
    #[Assert\Choice(
        choices: ['admin', 'manager', 'consultant'],
        message: 'Le rôle choisi n\'est pas valide'
    )]
    #[Groups(['user:detail', 'user:list', 'company:detail', 'company_user_role:write'])]
    private string $role;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
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

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;
        return $this;
    }
}
