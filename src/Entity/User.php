<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use App\DataProvider\UserDataProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use App\State\UserPasswordHasher;
use App\State\UserStateProcessor;


#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['user:list']],
            security: "is_granted('VIEW', null)",
            provider: UserDataProvider::class
        ),
        new Get(
            normalizationContext: ['groups' => ['user:read', 'user:detail']],
            security: "is_granted('VIEW', object)",
            securityMessage: "You cannot view this user."
        ),
        new Post(
            normalizationContext: ['groups' => ['user:read']],
            denormalizationContext: ['groups' => ['user:write']],
            security: "is_granted('ROLE_ADMIN') or is_granted('ROLE_MANAGER')",
            validationContext: ['groups' => ['Default', 'user:write']],
            processor: UserStateProcessor::class
        ),
        new Patch(
            normalizationContext: ['groups' => ['user:read']],
            denormalizationContext: ['groups' => ['user:update']],
            security: "is_granted('EDIT', object)",
            securityMessage: "You cannot edit this user.",
            validationContext: ['groups' => ['Default', 'user:update']],
            processor: UserPasswordHasher::class
        ),
        new Delete(
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: "Only admins can delete users."
        )
    ],
    normalizationContext: ['groups' => ['user:read']]
)]
#[UniqueEntity('email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    #[Groups(['user:read', 'user:list'])]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Groups(['user:read', 'user:list', 'user:write', 'user:update'])]
    #[Assert\NotBlank]
    #[Assert\Email]
    private string $email = '';

    #[ORM\Column(type: 'json')]
    #[Groups(['user:read', 'user:list', 'user:write', 'user:update'])]
    #[Assert\NotNull]
    private array $roles = [];

    #[ORM\Column(type: 'string', length: 255)]
    private string $password = '';

    #[Assert\NotBlank(groups: ['user:write'], message: 'Le mot de passe est obligatoire')]
    #[Assert\Length(
        min: 8,
        minMessage: 'Le mot de passe doit faire au moins {{ limit }} caractères',
        groups: ['user:write', 'user:update']
    )]
    #[Groups(['user:write', 'user:update'])]
    private ?string $plainPassword = null;

    #[ORM\OneToMany(
        targetEntity: CompanyUserRole::class,
        mappedBy: 'user',
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    #[Groups(['user:read', 'user:list'])]
    private Collection $companyUserRoles;

    public function __construct()
    {
        $this->roles = ['ROLE_CONSULTANT'];
        $this->companyUserRoles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        if (empty($email)) {
            throw new \InvalidArgumentException('Email cannot be null or empty');
        }
        $this->email = $email;
        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_CONSULTANT';
        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = array_unique($roles);
        return $this;
    }

    public function getPassword(): string
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

    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
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

    public function getCompanyId(): ?int
    {
        $role = $this->companyUserRoles->first();
        return $role ? $role->getCompany()->getId() : null;
    }

    public function getCompanyIds(): array
    {
        $companyIds = [];
        foreach ($this->companyUserRoles as $role) {
            $companyIds[] = $role->getCompany()->getId();
        }
        return $companyIds;
    }
}
