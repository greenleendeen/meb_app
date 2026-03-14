<?php

namespace App\Entity;

use App\Repository\CompteRenduRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\User;
use App\Entity\Intervention;
use App\Entity\Document;

#[ORM\Entity(repositoryClass: CompteRenduRepository::class)]
class CompteRendu


{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'text')]
    private ?string $description = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $dateCreation = null;

    //  #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'compteRendu')]
    // private ?User $technicien = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'compteRendu')]
#[ORM\JoinColumn(nullable: false)]
private ?User $technicien = null;



    #[ORM\ManyToOne(targetEntity: Intervention::class, inversedBy: 'compteRendus')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Intervention $intervention = null;

    #[ORM\OneToMany(mappedBy: 'compteRendu', targetEntity: Document::class, cascade: ['persist', 'remove'])]
    private Collection $documents;

    public function __construct()
    {
        $this->dateCreation = new \DateTimeImmutable();
        $this->documents = new ArrayCollection();
    }

    // --- Getters & Setters ---

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getDateCreation(): ?\DateTimeImmutable
    {
        return $this->dateCreation;
    }
    
    public function setDateCreation(\DateTimeImmutable $dateCreation): static
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    public function getTechnicien(): ?User
    {
        return $this->technicien;
    }

    public function setTechnicien(?User $technicien): static
    {
        $this->technicien = $technicien;
        return $this;
    }

    public function getIntervention(): ?Intervention
    {
        return $this->intervention;
    }

    public function setIntervention(?Intervention $intervention): static
    {
        $this->intervention = $intervention;
        return $this;
    }

    /**
     * @return Collection<int, Document>
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocument(Document $document): static
    {
        if (!$this->documents->contains($document)) {
            $this->documents->add($document);
            $document->setCompteRendu($this);
        }
        return $this;
    }
    public function removeDocument(Document $document): static
    {
        if ($this->documents->removeElement($document)) {
            if ($document->getCompteRendu() === $this) {
                $document->setCompteRendu(null);
            }
        }
        return $this;
    }
}
