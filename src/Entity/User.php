<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

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

    #[ORM\ManyToMany(targetEntity: Role::class, inversedBy: 'users')]
    #[ORM\JoinTable(name: "user_role")]
    private Collection $roles;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
    }

    // --- Getters/Setters ---
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

    // --- Méthodes imposées par UserInterface ---
    public function getUserIdentifier(): string
    {
        return (string) $this->identifiant; // utilisé pour la connexion
    }

    public function eraseCredentials(): void
    {
        // si tu stockes des infos sensibles en clair, nettoie-les ici
    }

    /**
     * Méthode imposée par UserInterface
     * Retourne un tableau de strings (noms des rôles).
     */
    public function getRoles(): array
    {
        $roles = $this->roles->map(fn(Role $r) => $r->getNom())->toArray();
        $roles[] = 'ROLE_USER'; // rôle par défaut
        return array_unique($roles);
    }

    /**
     * Méthode pour accéder aux entités Role réelles
     * Utile dans tes formulaires.
     *
     * @return Collection<int, Role>
     */
    public function getRolesEntities(): Collection
    {
        return $this->roles;
    }
public function setRolesEntities(Collection $roles): self
{
    $this->roles = $roles;
    return $this;
}
    public function addRole(Role $role): self
    {
        if (!$this->roles->contains($role)) {
            $this->roles->add($role);
        }
        return $this;
    }

    public function removeRole(Role $role): self
    {
        $this->roles->removeElement($role);
        return $this;
    }
}
