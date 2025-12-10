<?php

namespace App\Controller;

use App\Entity\Intervention;
use App\Entity\Document; // 
use App\Form\InterventionType;
use App\Repository\InterventionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use App\Service\PdfExtractor;
//Côté serveur : extraction du texte avec Smalot/pdfparser
use Smalot\PdfParser\Parser;
use Symfony\Component\HttpFoundation\File\UploadedFile;


#[Route('/intervention', name: 'app_intervention_')]
final class InterventionController extends AbstractController
{
    /** liste toutes les interventions */
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(InterventionRepository $interventionRepository): Response
    {
        return $this->render('intervention/index.html.twig', [
            'interventions' => $interventionRepository->findAll(),
        ]);
    }

    /** crée une nouvelle intervention  !!! il faudra ajouter $intervention->setCreatedBy($this->getUser()); pour gerer le 'user'*/ 
   #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $em, PdfExtractor $pdfExtractor): Response
{
    $intervention = new Intervention();
    $form = $this->createForm(InterventionType::class, $intervention, [
        'is_edit' => false
    ]);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $uploadDir = $this->getParameter('documents_directory');

        // 1) --- Traitement des documents fournis dans la collection 'documents' (DocumentType entries)
        $validDocuments = [];
        foreach ($intervention->getDocuments() as $document) {
            // DocumentType a un champ 'file' mapped => false
            $file = $document->getFile(); // UploadedFile|null (méthode getFile() dans l'entité Document attendue)
            if (!$file) {
                // si aucun fichier fourni pour cette entrée, on ignore/supprime l'entry
                continue;
            }

            $filename = uniqid('', true) . '.' . $file->guessExtension();
            $file->move($uploadDir, $filename);

            $document->setFilename($filename);
            $document->setPath('uploads/documents/' . $filename);
            $document->setIntervention($intervention);

            // extraction texte
            $fullPath = $uploadDir . '/' . $filename;
            $text = $pdfExtractor->extractText($fullPath);
            $document->setExtractedText($text);

            // extractions structurées
            $data = $pdfExtractor->extractData($text);
            if (!empty($data['client'])) {
                $intervention->setClientNom($data['client']);
            }
            if (!empty($data['adresse'])) {
                $intervention->setAdresse($data['adresse']);
            }
            if (!empty($data['numeroCommande'])) {
                $intervention->setReference($data['numeroCommande']);
            }

            $validDocuments[] = $document;
            $em->persist($document); // ok même si cascade persist présent, harmless
        }

        // 2) --- Traitement des fichiers envoyés via le champ newDocuments (multiple FileType unmapped)
        /** @var UploadedFile[] $uploadedFiles */
        $uploadedFiles = $form->get('newDocuments')->getData();
        if (is_array($uploadedFiles)) {
            foreach ($uploadedFiles as $file) {
                if (!$file) continue;
                $document = new Document();

                $filename = uniqid('', true) . '.' . $file->guessExtension();
                $file->move($uploadDir, $filename);

                $document->setFilename($filename);
                $document->setPath('uploads/documents/' . $filename);
                $document->setIntervention($intervention);

                $text = $pdfExtractor->extractText($uploadDir . '/' . $filename);
                $document->setExtractedText($text);

                $data = $pdfExtractor->extractData($text);
                if (!empty($data['client'])) {
                    $intervention->setClientNom($data['client']);
                }
                if (!empty($data['adresse'])) {
                    $intervention->setAdresse($data['adresse']);
                }
                if (!empty($data['numeroCommande'])) {
                    $intervention->setReference($data['numeroCommande']);
                }

                $validDocuments[] = $document;
                $em->persist($document);
            }
        }

        // remplacer la collection par les documents valides pour éviter les entrées vides
        $intervention->setDocuments(new \Doctrine\Common\Collections\ArrayCollection($validDocuments));

        // Persister l'intervention (et documents si cascade)
        $em->persist($intervention);
        $em->flush();

        $this->addFlash('success', 'Intervention créée avec succès ! Données extraites automatiquement.');

        return $this->redirectToRoute('app_intervention_show', ['id' => $intervention->getId()]);
    }

    return $this->render('intervention/new.html.twig', [
        'form' => $form,
    ]);
}

    /** Affiche le détail d'une intervention */
    #[Route('/{id<\d+>}', name: 'show', methods: ['GET'])]
    public function show(Intervention $intervention): Response
    {
        //show.html
        return $this->render('intervention/show.html.twig', [
            'intervention' => $intervention,
            'documents' => $intervention->getDocuments(),
        ]);
    }

    /** Modifie une intervention existante */
   #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
public function edit(
    Request $request,
    Intervention $intervention,
    EntityManagerInterface $em,
    PdfExtractor $pdfExtractor
): Response {
    $form = $this->createForm(InterventionType::class, $intervention, [
        'is_edit' => true
    ]);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $uploadDir = $this->getParameter('documents_directory');

        // 1) nouveaux fichiers depuis newDocuments (multiple FileType unmapped)
        /** @var UploadedFile[] $uploadedFiles */
        $uploadedFiles = $form->get('newDocuments')->getData();
        if (is_array($uploadedFiles)) {
            foreach ($uploadedFiles as $file) {
                if (!$file) continue;
                $document = new Document();

                $filename = uniqid('', true) . '.' . $file->guessExtension();
                $file->move($uploadDir, $filename);

                $document->setFilename($filename);
                $document->setPath('uploads/documents/' . $filename);
                $document->setIntervention($intervention);

                $text = $pdfExtractor->extractText($uploadDir . '/' . $filename);
                $document->setExtractedText($text);

                $em->persist($document);
            }
        }

        // Optionnel : si tu autorises modification de documents existants via la collection,
        // il faudrait traiter les fichiers fournis dans chaque DocumentType entry (similaire à new()).
        // Ici on suppose que l'on ne modifie pas l'ancien fichier d'un Document existant mais on peut l'ajouter.

        $em->persist($intervention);
        $em->flush();

        $this->addFlash('success', 'Intervention mise à jour avec succès !');
        return $this->redirectToRoute('app_intervention_show', ['id' => $intervention->getId()]);
    }

    return $this->render('intervention/edit.html.twig', [
        'form' => $form,
        'intervention' => $intervention,
    ]);
}

    /** Supprime une intervention (via POST + token CSRF)  */
    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Intervention $intervention, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $intervention->getId(), $request->request->get('_token'))) {
            $em->remove($intervention);
            $em->flush();
            $this->addFlash('success', 'Intervention supprimée avec succès.');
        }

        return $this->redirectToRoute('app_intervention_show', ['id' => $intervention->getId(),]);
    }
}
