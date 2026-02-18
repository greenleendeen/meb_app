<?php

namespace App\Controller;

use App\Entity\Document;
use App\Form\DocumentType;
use App\Service\DocumentManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Intervention;
//use App\Form\DocumentType;
use App\Form\InterventionType;
use App\Repository\DocumentRepository;
use App\Service\PdfExtractor;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/document', name: 'app_document_')]
final class DocumentController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private DocumentManager $documentManager
    ) {}

    /**
     * Liste des documents
     */
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        $documents = $this->em->getRepository(Document::class)->findAll();

        return $this->render('document/index.html.twig', [            'documents' => $documents,
        ]);
    }

    /**
     * Création d’un document (upload + rattachement intervention)
     */
    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $document = new Document();

        $form = $this->createForm(DocumentType::class, $document);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var UploadedFile|null $file */
            $file = $document->getFile();

            if (!$file instanceof UploadedFile) {
                $this->addFlash('danger', 'Aucun fichier sélectionné.');
                return $this->redirectToRoute('app_document_new');
            }

            /**
             * Les données intervention peuvent venir :
             * - du formulaire
             * - d’un PDF extrait
             * - ou être saisies manuellement
             *
             * On les centralise ici
             */
            $interventionData = [
                'reference' => $request->request->get('reference'),
                'clientNom' => $request->request->get('clientNom'),
                'adresse'   => $request->request->get('adresse'),
                'demande'   => $request->request->get('demande'),
                'detail'    => $request->request->get('detail'),
            ];

            /**
             *LOGIQUE CENTRALE
             * - upload
             * - extraction PDF
             * - lien intervention
             *
             * => TOUT passe par DocumentManager
             */
           // $this->documentManager->uploadFile($document, $file);
          //  $this->documentManager->attachToIntervention($document, $interventionData);
          $this->documentManager->handleUploadedDocument(
    $document,
    $file,
      null //  pas d’intervention ici
);

            $this->em->persist($document);
            $this->em->flush();

            $this->addFlash('success', 'Document ajouté avec succès.');

            return $this->redirectToRoute('app_document_show', [
                'id' => $document->getId()
            ]);
        }

        return $this->render('document/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Affichage d’un document
     */
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Document $document): Response
    {
        return $this->render('document/show.html.twig', [
            'document' => $document,
        ]);
    }

    /**
     * Édition d’un document
     *
     * BONNES IDÉES CONSERVÉES :
     * - document existant sans nouveau fichier → on garde
     * - nouveau fichier → on remplace proprement
     * - pas de fichier → pas d’upload
     */
    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Document $document): Response
    {
        $form = $this->createForm(DocumentType::class, $document);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var UploadedFile|null $file */
            $file = $document->getFile();

            /**
             * CAS A :
             * Document existant sans nouveau fichier
             * → on ne touche pas à l’upload
             */
            if ($file instanceof UploadedFile) {
                /**
                 * CAS B :
                 * Nouveau fichier → upload + remplacement
                 */
               // $this->documentManager->uploadFile($document, $file);
            }

            // Les relations Intervention / CompteRendu ne changent pas ici
            $this->em->flush();

            $this->addFlash('success', 'Document mis à jour.');

            return $this->redirectToRoute('app_document_show', [
                'id' => $document->getId()
            ]);
        }

        return $this->render('document/edit.html.twig', [
            'form' => $form->createView(),
            'document' => $document,
        ]);
    }

    /**
     * Suppression HARD (pour l’instant)
     *
     * À ÉVOLUTION FUTURE :
     * - soft delete (deletedAt)
     * - suppression fichier physique
     */
    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Document $document): Response
    {
        if ($this->isCsrfTokenValid('delete' . $document->getId(), $request->request->get('_token'))) {
            $this->em->remove($document);
            $this->em->flush();

            $this->addFlash('success', 'Document supprimé.');
        }

        return $this->redirectToRoute('app_document_index');
    }
}
