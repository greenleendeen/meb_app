<?php

namespace App\Entity;

use App\Enum\DocumentType as DocumentEnum;
use App\Repository\DocumentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[ORM\Entity(repositoryClass: DocumentRepository::class)]
class Document
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $filename = null; // nom TECHNIQUE (serveur)

    #[ORM\Column(length: 255)]
    private ?string $originalName = null; // nom UTILISATEUR null pour commencer 

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $path = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $extractedText = null;

    #[ORM\Column(enumType: DocumentEnum::class, nullable: false)]
    private ?DocumentEnum $type = null;

    #[ORM\ManyToOne(inversedBy: 'documents')]
    #[ORM\JoinColumn(nullable: false)]
    private ?\App\Entity\Intervention $intervention = null;

    #[ORM\ManyToOne(inversedBy: 'documents')]
    private ?\App\Entity\CompteRendu $compteRendu = null;

    private ?UploadedFile $file = null;

    // --- GETTERS / SETTERS ---

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(?string $filename): static
    {
        $this->filename = $filename;
        return $this;
    }

    public function getDisplayName(): string
    {
        return $this->originalName ?? $this->filename;
    }
    public function getOriginalName(): ?string
    {
        return $this->originalName;
    }

    public function setOriginalName(?string $originalName): static
    {
        $this->originalName = $originalName;
        return $this;
    }


    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): static
    {
        $this->path = $path;
        return $this;
    }

    public function getExtractedText(): ?string
    {
        return $this->extractedText;
    }

    public function setExtractedText(?string $extractedText): static
    {
        $this->extractedText = $extractedText;
        return $this;
    }

    public function getType(): ?DocumentEnum
    {
        return $this->type;
    }

    public function setType(?DocumentEnum $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getIntervention(): ?\App\Entity\Intervention
    {
        return $this->intervention;
    }

    public function setIntervention(?\App\Entity\Intervention $intervention): static
    {
        $this->intervention = $intervention;
        return $this;
    }

    public function getCompteRendu(): ?\App\Entity\CompteRendu
    {
        return $this->compteRendu;
    }

    public function setCompteRendu(?\App\Entity\CompteRendu $compteRendu): static
    {
        $this->compteRendu = $compteRendu;
        return $this;
    }

    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    public function setFile(?UploadedFile $file): static
    {
        $this->file = $file;
        return $this;
    }

    // --- LABELS pour Twig ---
   // public function getTypeLabel(): string
  //  {
  //      return match ($this->type) {
   //         DocumentEnum::BON_COMMANDE => 'Bon de commande',
  //          DocumentEnum::DEVIS => 'Devis',
  //          DocumentEnum::PHOTO => 'Photo',
     //       DocumentEnum::COMPTE_RENDU => 'Compte rendu',
   //         DocumentEnum::FACTURE => 'Facture',
           // DocumentEnum::AUTRE => 'autre',
   //         default => 'Inconnu',
   //     };
  //  }
}
