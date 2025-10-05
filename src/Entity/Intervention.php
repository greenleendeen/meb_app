<?php

namespace App\Entity;

use App\Repository\InterventionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use App\Entity\Document;

#[ORM\Entity(repositoryClass: InterventionRepository::class)]
class Intervention
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $clientNom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $reference = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adresse = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $demande = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $detail = null;

   /**
 * @var Collection<int, Document>
 */
#[ORM\OneToMany(targetEntity: Document::class, mappedBy: 'intervention', cascade: ['persist', 'remove'])]
private Collection $documents; // <- nom au pluriel


public function __construct()
{
    $this->documents = new ArrayCollection();
}

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
        $document->setIntervention($this);
    }
    return $this;
}

public function removeDocument(Document $document): static
{
    if ($this->documents->removeElement($document)) {
        if ($document->getIntervention() === $this) {
            $document->setIntervention(null);
        }
    }
    return $this;
}


}
