<?php

namespace App\Controller;

use App\Entity\Intervention;
use App\Entity\Document; // 
use App\Entity\CompteRendu;
use App\Form\InterventionType;
use App\Form\CompteRenduType;
use App\Repository\InterventionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\PdfExtractor;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use App\Service\DocumentManager;

//Côté serveur : extraction du texte avec Smalot/pdfparser
use Smalot\PdfParser\Parser;
use Symfony\Component\HttpFoundation\File\UploadedFile;


#[Route('/intervention', name: 'app_intervention_')]
final class InterventionController extends AbstractController
{
    public function __construct(
        private DocumentManager $documentManager
    ) {}

    /** liste toutes les interventions */
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(InterventionRepository $interventionRepository): Response
    {
        return $this->render('intervention/index.html.twig', [
            'interventions' => $interventionRepository->findAll(),
        ]);
    }

    /** Création d’une nouvelle intervention */
    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $intervention = new Intervention();

        $form = $this->createForm(InterventionType::class, $intervention, [
            'is_edit' => false,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            foreach ($intervention->getDocuments() as $document) {
                $file = $document->getFile();

                if ($file instanceof UploadedFile) {
                    $this->documentManager->handleUploadedDocument(
                        $document,
                        $file,
                        $intervention
                    );
                }
            }

            // TODO plus tard :
            // $intervention->setCreatedBy($this->getUser());

            $em->persist($intervention);
            $em->flush();

            $this->addFlash('success', 'Intervention créée avec succès.');

            return $this->redirectToRoute('app_intervention_show', [
                'id' => $intervention->getId()
            ]);
        }

        return $this->render('intervention/new.html.twig', [
            'form' => $form,
        ]);
    }

    /** Affichage détail */
    #[Route('/{id<\d+>}', name: 'show', methods: ['GET'])]
    public function show(Intervention $intervention): Response
    {
        return $this->render('intervention/show.html.twig', [
            'intervention' => $intervention,
            'documents' => $intervention->getDocuments(),
        ]);
    }

    /** Modification d’une intervention */
    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Intervention $intervention,
        EntityManagerInterface $em
    ): Response {
        $form = $this->createForm(InterventionType::class, $intervention, [
            'is_edit' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            foreach ($intervention->getDocuments() as $document) {
                $file = $document->getFile();

                // CAS A : document existant, pas de nouveau fichier
               //f ($document->getId() && !$file instanceof UploadedFile) {
              //    continue;
             // }

                /**
                 * CAS 1 : document EXISTANT + nouveau fichier
                 * → INTERDIT (remplacement)
                 */
                if ($document->getId() !== null && $file instanceof UploadedFile) {
                    $this->addFlash(
                        'warning',
                        'Un document existant ne peut pas être remplacé. Supprimez-le puis ajoutez-en un nouveau.'
                    );

                    return $this->redirectToRoute('app_intervention_edit', [
                        'id' => $intervention->getId(),
                    ]);
                }

                /**
                 * CAS 2 : NOUVEAU document + fichier
                 * → OK (ajout)
                 */
                if ($document->getId() === null && $file instanceof UploadedFile) {
                    $this->documentManager->handleUploadedDocument(
                        $document,
                        $file,
                        $intervention
                    );

                    $em->persist($document);
                }

                /**
                 * CAS 3 : document existant sans fichier
                 * → on ne touche à rien
                 */

            }
            // CAS C : document vide → suppression
            //if (!$document->getId() && !$file instanceof UploadedFile) {
            //    $intervention->removeDocument($document);
            //     continue;
            // }

            // CAS B : nouveau fichier
            //  if ($file instanceof UploadedFile) {
            //      $this->documentManager->handleUploadedDocument(
            //         $document,
            //         $file,
            //         $intervention
            //      );
            //   }
            // }

            // Suppression explicite via checkbox
            $deletedIds = $request->request->all('documents_delete', []);
            foreach ($deletedIds as $id) {
                $doc = $em->getRepository(Document::class)->find($id);
                if ($doc) {
                    $em->remove($doc);
                }
            }

            $em->persist($intervention);
            $em->flush();

            $this->addFlash('success', 'Intervention mise à jour.');

            return $this->redirectToRoute('app_intervention_show', [
                'id' => $intervention->getId()
            ]);
        }

        return $this->render('intervention/edit.html.twig', [
            'form' => $form,
            'intervention' => $intervention,
        ]);
    }

    /** Suppression */
    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Intervention $intervention,
        EntityManagerInterface $em
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $intervention->getId(), $request->request->get('_token'))) {
            $em->remove($intervention);
            $em->flush();
            $this->addFlash('success', 'Intervention supprimée.');
        }

        return $this->redirectToRoute('app_intervention_show', [
            'id' => $intervention->getId(),
        ]);
    }

    /** Nouveau compte-rendu */
    #[Route('/intervention/{id}/compte-rendu/new', name: 'app_compte_rendu_new')]
    public function newCompteRendu(
        Intervention $intervention,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $compteRendu = new CompteRendu();
        $compteRendu->setIntervention($intervention);
        $compteRendu->setTechnicien($this->getUser());

        $form = $this->createForm(CompteRenduType::class, $compteRendu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            foreach ($compteRendu->getDocuments() as $doc) {
                $doc->setIntervention($intervention);
                $doc->setCompteRendu($compteRendu);
            }

            $em->persist($compteRendu);
            $em->flush();

            return $this->redirectToRoute('app_intervention_show', [
                'id' => $intervention->getId()
            ]);
        }

        return $this->render('compte_rendu/new.html.twig', [
            'intervention' => $intervention,
            'form' => $form,
        ]);
    }
}
