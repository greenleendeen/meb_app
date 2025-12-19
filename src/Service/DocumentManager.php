<?php

namespace App\Service;

use App\Entity\Document;
use App\Entity\Intervention;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Service\PdfExtractor;

class DocumentManager
{
    private string $uploadDir;

    public function __construct(
        private EntityManagerInterface $em,
        private PdfExtractor $pdfExtractor,
        private string $documentsDirectory,
    ) 
    //?
    {
         $this->uploadDir = rtrim($documentsDirectory, '/');
       // $this->uploadDir = $documentsDirectory;
    }
 public function uploadFile(Document $document, UploadedFile $file): void
    {
        if (!$file) {
            throw new \InvalidArgumentException('Aucun fichier fourni');
        }

        // Nom technique unique
        $filename = uniqid('', true) . '.' . $file->guessExtension();

        // Déplacement fichier
        $file->move($this->uploadDir, $filename);

        // Infos fichier
        $document->setFilename($filename);
        $document->setOriginalName($file->getClientOriginalName());
        $document->setPath('/uploads/documents/' . $filename);

        // Extraction texte PDF (optionnelle)
        if ($file->getClientOriginalExtension() === 'pdf') {
            try {
                $text = $this->pdfExtractor->extractText(
                    $this->uploadDir . '/' . $filename
                );
                $document->setExtractedText($text);
            } catch (\Throwable $e) {
                // On ne bloque PAS l’upload si l’extraction échoue
                $document->setExtractedText(null);
            }
        }

        // Persist ici (mais PAS de flush)
      //  $this->em->persist($document);
    }

    /**
     * Lie un document à une intervention existante ou nouvelle
     */
    public function attachToIntervention(
        Document $document,
        array $data
    ): Intervention {
        if (empty($data['reference'])) {
            throw new \InvalidArgumentException('Référence intervention obligatoire');
        }

        $intervention = $this->em
            ->getRepository(Intervention::class)
            ->findOneBy(['reference' => $data['reference']]);

        if (!$intervention) {
            $intervention = new Intervention();
            $intervention->setReference($data['reference']);
            $intervention->setClientNom($data['clientNom'] ?? '');
            $intervention->setAdresse($data['adresse'] ?? '');
            $intervention->setDemande($data['demande'] ?? '');
            $intervention->setDetail($data['detail'] ?? '');

            $this->em->persist($intervention);
            
        }

        $document->setIntervention($intervention);
        $intervention->addDocument($document);

        return $intervention;
    }

   
}

 /**
     * Crée un document et le lie à une intervention existante ou nouvelle.
     * Retourne le Document créé.
     */
   /* public function createDocumentWithIntervention(
        UploadedFile $file,
        array $interventionData
    ): Document {
        // Cherche intervention existante par référence
        $intervention = $this->em->getRepository(Intervention::class)
            ->findOneBy(['reference' => $interventionData['reference'] ?? '']);

        if (!$intervention) {
            // Crée une nouvelle intervention
            $intervention = new Intervention();
            $intervention->setReference($interventionData['reference'] ?? '');
            $intervention->setClientNom($interventionData['clientNom'] ?? '');
            $intervention->setAdresse($interventionData['adresse'] ?? '');
            $intervention->setDemande($interventionData['demande'] ?? '');
            $intervention->setDetail($interventionData['detail'] ?? '');
            $this->em->persist($intervention);
        }

        // Génère un nom unique pour le fichier
        $filename = uniqid('', true) . '.' . $file->guessExtension();
        $file->move($this->documentsDirectory, $filename);

        // Crée l'entité Document
        $document = new Document();
        $document->setFilename($filename);
        $document->setOriginalName($file->getClientOriginalName());
        $document->setPath('documents'); // dossier constant
        $document->setIntervention($intervention);
        $intervention->addDocument($document);

        // Extraction du texte PDF si nécessaire
        if ($file->getClientOriginalExtension() === 'pdf') {
            $document->setExtractedText($this->pdfExtractor->extractText($this->documentsDirectory . '/' . $filename));
        }

        $this->em->persist($document);

        return $document;
    }
}
*/

    /**
     * Étape 1 :  upload fichier + création entité Document.
     * Sans extraction, sans intervention.
     */
   /* public function createDocument(?UploadedFile $file, ?Intervention $intervention = null): Document
    {
        if (!$file) {
            throw new \InvalidArgumentException("Aucun fichier fourni à DocumentManager::createDocument()");
        }

        // Nom fichier unique
        $filename = uniqid('', true) . '.' . $file->guessExtension();

        // Upload sécurisé
        try {
            $file->move($this->uploadDir, $filename);
        } catch (\Exception $e) {
            throw new \RuntimeException("Échec de l'upload du fichier : " . $e->getMessage());
        }

        // Création entité Document
        $document = new Document();
        $document->setFilename($filename);

        $document->setOriginalName($file->getClientOriginalName()); // nouveau champ
        $document->setPath('documents'); // dossier constant

        // Chemin accessible par le navigateur
        // $document->setPath('/uploads/documents/' . $filename);
            if ($intervention) {
        $document->setIntervention($intervention);
        $intervention->addDocument($document);
    }

        // Enregistrement base
        $this->em->persist($document);

        return $document;
    }
}
*/