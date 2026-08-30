<?php

namespace App\Service;

use App\Entity\Document;
use App\Entity\Intervention;
use App\Entity\CompteRendu;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class DocumentManager
{
    private string $uploadDir;

    public function __construct(
        private EntityManagerInterface $em,
        private PdfExtractor $pdfExtractor,
        string $documentsDirectory
    ) {
        $this->uploadDir = rtrim($documentsDirectory, '/');
    }

    public function handleUploadedDocument(
    Document $document,
    UploadedFile $file,
    ?Intervention $intervention,
    ?CompteRendu $compteRendu,
): void {
    $this->uploadFile($document, $file);

    if ($intervention !== null) {
        $document->setIntervention($intervention);
    }
     if ($compteRendu !== null) {
        $document->setCompteRendu($compteRendu);
    }

    $document->setUploadedAt(new \DateTimeImmutable());
}

    private function uploadFile(Document $document, UploadedFile $file): void
    {
        $filename = uniqid('', true) . '.' . ($file->guessExtension() ?? 'bin');

        $file->move($this->uploadDir, $filename);

        $document->setFilename($filename);
        $document->setOriginalName($file->getClientOriginalName());
        $document->setPath('/uploads/documents/' . $filename);

        if ($file->getClientOriginalExtension() === 'pdf') {
            try {
                $text = $this->pdfExtractor->extractText(
                    $this->uploadDir . '/' . $filename
                );
                $document->setExtractedText($text);
            } catch (\Throwable) {
                $document->setExtractedText(null);
            }
        }
    }
/*
    public function handleUploadedDocument(
        Document $document,
        UploadedFile $file,
        ?Intervention $intervention
    ): void {

        // PROTECTION ANTI-ÉCRASEMENT
    if (
    $document->getId() !== null
    && $document->getFilename() !== null
    && $document->getUploadedAt() !== null
) {
    throw new \LogicException(
        'Ce document existe déjà. Vous ne pouvez pas remplacer son fichier.'
    );
}

        $this->uploadFile($document, $file);

        if ($intervention !== null) {
            $document->setIntervention($intervention);
        }

        $document->setUploadedAt(new \DateTimeImmutable());
        // PAS de persist ici  $this->em->persist($document);
    }
*/
    
}

