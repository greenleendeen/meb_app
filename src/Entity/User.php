<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\CompteRendu;
use App\Entity\Role;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $nom = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $identifiant = null;

    #[ORM\Column]
    private ?string $password = null;

    // --- Relation vers les comptes rendus ---
    #[ORM\OneToMany(mappedBy: 'technicien', targetEntity: CompteRendu::class)]
    private Collection $compteRendu;

    // --- Relation ManyToMany vers Role (si nécessaire pour gérer roles personnalisés) ---
    #[ORM\ManyToMany(targetEntity: Role::class, inversedBy: 'users')]
    private Collection $roles;

    public function __construct()
    {
        $this->compteRendu = new ArrayCollection();
        $this->roles = new ArrayCollection();
    }

    // --- Getters / Setters principaux ---
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getIdentifiant(): ?string
    {
        return $this->identifiant;
    }

    public function setIdentifiant(string $identifiant): static
    {
        $this->identifiant = $identifiant;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    // --- Méthodes Symfony Security ---
    public function getUserIdentifier(): string
    {
        return (string) $this->identifiant;
    }

    public function eraseCredentials(): void
    {
        // pas d'info sensible à effacer ici
    }

    // --- Comptes rendus ---
    /**
     * @return Collection<int, CompteRendu>
     */
    public function getCompteRendus(): Collection
    {
        return $this->compteRendu;
    }

    public function addCompteRendu(CompteRendu $compteRendu): static
    {
        if (!$this->compteRendu->contains($compteRendu)) {
            $this->compteRendu->add($compteRendu);
            $compteRendu->setTechnicien($this);
        }
        return $this;
    }

    public function removeCompteRendu(CompteRendu $compteRendu): static
    {
        if ($this->compteRendu->removeElement($compteRendu)) {
            if ($compteRendu->getTechnicien() === $this) {
                $compteRendu->setTechnicien(null);
            }
        }
        return $this;
    }

    // --- Roles (ManyToMany) ---
    /**
     * @return Collection<int, Role>
     */
    public function getRolesEntity(): Collection
    {
        return $this->roles;
    }

    public function addRole(Role $role): static
    {
        if (!$this->roles->contains($role)) {
            $this->roles->add($role);
        }
        return $this;
    }

    public function removeRole(Role $role): static
    {
        $this->roles->removeElement($role);
        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles->map(fn(Role $r) => $r->getNom())->toArray();
        $roles[] = 'ROLE_USER'; // rôle par défaut
        return array_unique($roles);
    }
}
