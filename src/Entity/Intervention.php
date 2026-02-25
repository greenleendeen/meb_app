<?php

namespace App\Entity;

use App\Repository\InterventionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

use App\Entity\Document;

#[ORM\Entity(repositoryClass: InterventionRepository::class)]
#[UniqueEntity(
    fields: ['reference'],
    message: 'Une intervention avec cette référence existe déjà.'
)]
class Intervention
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $clientNom = null;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private ?string $reference = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adresse = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $demande = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $detail = null;

    // j'ajoute les champs manquants dans l'entité; je mets nullable: true - tant que je n'ai pas encore tous les formulaires et fixtures mis à jour.
    //Ça évitera une erreur SQL du type "column cannot be null" pendant les premiers tests.

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $dateIntervention = null;

    #[ORM\Column(type: 'time', nullable: true)]
    private ?\DateTimeInterface $heureDebut = null;

    #[ORM\Column(type: 'time', nullable: true)]
    private ?\DateTimeInterface $heureFin = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $technicien = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    //l'utilisateur créateur de l'intervention 
    //#[ORM\ManyToOne(targetEntity: User::class)]
    //#[ORM\JoinColumn(nullable: true)]
    //private ?User $createdBy = null;

    // #[ORM\OneToMany(mappedBy: 'intervention', targetEntity: CompteRendu::class, cascade: ['persist', 'remove'])]
    #[ORM\OneToMany(mappedBy: 'intervention', targetEntity: CompteRendu::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['dateCreation' => 'DESC'])]
    private Collection $compteRendus; // pluriel

    public function __construct()
    {
        $this->compteRendus = new ArrayCollection();
        $this->documents = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->interventionHistories = new ArrayCollection();
    }

    /**
     * @return Collection<int, CompteRendu>
     */
    public function getCompteRendus(): Collection
    {
        return $this->compteRendus;
    }

    public function addCompteRendu(CompteRendu $compteRendu): static
    {
        if (!$this->compteRendus->contains($compteRendu)) {
            $this->compteRendus->add($compteRendu);
            $compteRendu->setIntervention($this);
        }
        return $this;
    }

    public function removeCompteRendu(CompteRendu $compteRendu): static
    {
        if ($this->compteRendus->removeElement($compteRendu)) {
            if ($compteRendu->getIntervention() === $this) {
                $compteRendu->setIntervention(null);
            }
        }
        return $this;
    }

    /**
     * @var Collection<int, Document>
     */
    #[ORM\OneToMany(targetEntity: Document::class, mappedBy: 'intervention', cascade: ['persist', 'remove'])]
    private Collection $documents;

    /**
     * @var Collection<int, InterventionHistory>
     */
    #[ORM\OneToMany(mappedBy: 'intervention', targetEntity: InterventionHistory::class, cascade: ['persist'], orphanRemoval: false)]
    #[ORM\OrderBy(['modifiedAt' => 'DESC'])]
    private Collection $interventionHistories;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClientNom(): ?string
    {
        return $this->clientNom;
    }

    public function setClientNom(?string $clientNom): static
    {
        $this->clientNom = $clientNom;
        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): static
    {
        $this->reference = $reference;
        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): static
    {
        $this->adresse = $adresse;
        return $this;
    }

    public function getDemande(): ?string
    {
        return $this->demande;
    }

    public function setDemande(?string $demande): static
    {
        $this->demande = $demande;
        return $this;
    }

    public function getDetail(): ?string
    {
        return $this->detail;
    }

    public function setDetail(?string $detail): static
    {
        $this->detail = $detail;
        return $this;
    }
    // pour le tris suivant la date de création 
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return Collection<int, Document>
     */
    public function getDocuments(): Collection
    {
        return $this->documents->filter(
            fn(Document $doc) => !$doc->isDeleted()
        );
    }

    public function setDocuments(Collection $documents): static
    {
        // Simplement remplacer la collection sans détacher les anciens documents
        $this->documents = $documents;

        // Assurer la liaison bidirectionnelle si nécessaire
        foreach ($documents as $document) {
            if ($document->getIntervention() !== $this) {
                $document->setIntervention($this);
            }
        }

        return $this;
    }

    public function addDocument(Document $document): static
    {
        if (!$this->documents->contains($document)) {
            $this->documents->add($document);
            $document->setIntervention($this);
        }
        return $this;
    }

    public function removeDocument(Document $document): static
    {
        if ($this->documents->removeElement($document)) {

            // cohérence Doctrine (owning side)
            if ($document->getIntervention() === $this) {
                $document->setIntervention($this);
            }

            // logique métier
            $document->softDelete();
        }

        return $this;
    }

    /* public function removeDocument(Document $document): static
    {
        if ($this->documents->removeElement($document)) {
            if ($document->getIntervention() === $this) {
                $document->setIntervention(null);
            }
        }
        return $this;
    }*/

    // --- Getters / Setters pour datetime, heuredebut, heurefin et technicien---

    public function getDateIntervention(): ?\DateTimeInterface
    {
        return $this->dateIntervention;
    }

    public function setDateIntervention(?\DateTimeInterface $dateIntervention): self
    {
        $this->dateIntervention = $dateIntervention;
        return $this;
    }

    public function getHeureDebut(): ?\DateTimeInterface
    {
        return $this->heureDebut;
    }

    public function setHeureDebut(?\DateTimeInterface $heureDebut): self
    {
        $this->heureDebut = $heureDebut;
        return $this;
    }

    public function getHeureFin(): ?\DateTimeInterface
    {
        return $this->heureFin;
    }

    public function setHeureFin(?\DateTimeInterface $heureFin): self
    {
        $this->heureFin = $heureFin;
        return $this;
    }

    public function getTechnicien(): ?User
    {
        return $this->technicien;
    }

    public function setTechnicien(?User $technicien): self
    {
        $this->technicien = $technicien;
        return $this;
    }

    /**
     * @return Collection<int, InterventionHistory>
     */
    public function getInterventionHistories(): Collection
    {
        return $this->interventionHistories;
    }

    public function addInterventionHistory(InterventionHistory $interventionHistory): static
    {
        if (!$this->interventionHistories->contains($interventionHistory)) {
            $this->interventionHistories->add($interventionHistory);
            $interventionHistory->setIntervention($this);
        }

        return $this;
    }

    public function removeInterventionHistory(InterventionHistory $interventionHistory): static
    {
        if ($this->interventionHistories->removeElement($interventionHistory)) {
            // set the owning side to null (unless already changed)
            if ($interventionHistory->getIntervention() === $this) {
                $interventionHistory->setIntervention(null);
            }
        }

        return $this;
    }
}
