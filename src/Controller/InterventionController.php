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

            //  On prépare un tableau pour stocker uniquement les documents valides
            $validDocuments = [];
            dump($form->getErrors(true, false));
            //  Parcours de tous les documents soumis dans le formulaire
            foreach ($intervention->getDocuments() as $document) {

                /** @var UploadedFile|null $file */
                $file = $document->getFile();

                //  Si aucun fichier n’a été uploadé → on ignore ce document
                if (!$file) {
                    $intervention->removeDocument($document); //  supprime proprement
                    continue;
                }

                //  Générer un nom unique pour le fichier
                $filename = uniqid('', true) . '.' . $file->guessExtension();

                //  Déplacer le fichier dans le dossier d’upload
                $uploadDir = $this->getParameter('documents_directory');
                $file->move($uploadDir, $filename);

                //  Enregistrer les infos dans l’entité Document
                $document->setFilename($filename);
                $document->setPath('uploads/documents/' . $filename);
                $document->setIntervention($intervention);

                //  Extraction automatique du texte
                $fullPath = $uploadDir . '/' . $filename;
                $text = $pdfExtractor->extractText($fullPath);
                $document->setExtractedText($text);

                //  Extraire des données structurées
                $data = $pdfExtractor->extractData($text);
                if (isset($data['client'])) {
                    $intervention->setClientNom($data['client']);
                }
                if (isset($data['adresse'])) {
                    $intervention->setAdresse($data['adresse']);
                }
                if (isset($data['numeroCommande'])) {
                    $intervention->setReference($data['numeroCommande']);
                }

                //  Ajouter le document traité dans la liste finale
                $validDocuments[] = $document;

                $em->persist($document);
            }

            // Remplacer la collection de documents par uniquement les valides
            $intervention->setDocuments(new \Doctrine\Common\Collections\ArrayCollection($validDocuments));

            //  Enregistrer en base

            $em->persist($intervention);
            $em->flush();

            $this->addFlash('success', 'Intervention créée avec succès ! Données extraites automatiquement.');

            //
            //  dd($intervention->getId());

            //  Redirection après succès
            return $this->redirectToRoute('app_intervention_show', [
                'id' => $intervention->getId(),
            ]);
        }

        //  Affichage du formulaire
        return $this->render('intervention/new.html.twig', [
            'form' => $form,
        ]);
    }


    /** Affiche le détail d'une intervention */
    #[Route('/{id}', name: 'show', methods: ['GET'])]
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

            // Traiter tous les documents liés à cette intervention
            foreach ($intervention->getDocuments() as $document) {
                $file = $document->getFile(); // UploadedFile ou null

                // Ignorer les documents sans fichier
                if (!$file) {
                    continue;
                }

                /** @var UploadedFile|null $file */
                $file = $document->getFile();
                if (!$file) continue; // Aucun fichier, on ignore ce document

                // Générer un nom unique et déplacer le fichier
                $filename = uniqid('', true) . '.' . $file->guessExtension();
                $file->move($this->getParameter('documents_directory'), $filename);

                // Enregistrer les infos dans l’entité
                $document->setFilename($filename);
                $document->setPath('uploads/documents/' . $filename);
                $document->setIntervention($intervention);

                // Extraction automatique via service
                $text = $pdfExtractor->extractText($this->getParameter('documents_directory') . '/' . $filename);
                $document->setExtractedText($text);


                // Optionnel : extraire des données structurées et remplir le formulaire
                $data = $pdfExtractor->extractData($text);
                if (isset($data['client'])) {
                    $intervention->setClientNom($data['client']);
                }
                if (isset($data['adresse'])) {
                    $intervention->setAdresse($data['adresse']);
                }
                if (isset($data['numeroCommande'])) {
                    $intervention->setReference($data['numeroCommande']);
                }
            }

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
