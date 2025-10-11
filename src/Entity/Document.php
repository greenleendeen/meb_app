<?php

namespace App\Entity;

use App\Enum\DocumentType;
use App\Repository\DocumentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DocumentRepository::class)]
class Document
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $filename = null;

    #[ORM\Column(length: 255)]
    private ?string $path = null;

    #[ORM\Column(enumType: DocumentType::class, nullable: false)]
private ?DocumentType $type = null;

#[ORM\ManyToOne(inversedBy: 'documents')]
private ?CompteRendu $compteRendu = null;

    #[ORM\ManyToOne(inversedBy: 'documents')]
    private ?Intervention $intervention = null;

    public function getIntervention(): ?Intervention
{
    return $this->intervention;
}

public function setIntervention(?Intervention $intervention): static
{
    $this->intervention = $intervention;
    return $this;
}

public function getType(): ?DocumentType
{
    return $this->type;
}

public function setType(?DocumentType $type): static
{
    $this->type = $type;
    return $this;
}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): static
    {
        $this->filename = $filename;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): static
    {
        $this->path = $path;

        return $this;
    }

     //  nouveau getter pour afficher un label lisible dans twig (show.html.twig)
   public function getTypeLabel(): string
{
    return match($this->type) {
        DocumentType::BON_COMMANDE => 'Bon de commande',
        DocumentType::DEVIS => 'Devis',
        DocumentType::PHOTO => 'Photo',
        DocumentType::COMPTE_RENDU => 'Compte rendu',
        DocumentType::FACTURE => 'Facture',
    };
}

  
}
