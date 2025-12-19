<?php

namespace App\Controller;

use App\Entity\Document;
use App\Entity\Intervention;
use App\Form\DocumentType;
use App\Form\InterventionType;
use App\Repository\DocumentRepository;
use App\Service\PdfExtractor;
use App\Service\DocumentManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/document')]

final class DocumentController extends AbstractController
{
    private DocumentManager $documentManager;
    private PdfExtractor $pdfExtractor;
     private EntityManagerInterface $em;

    public function __construct(DocumentManager $documentManager, PdfExtractor $pdfExtractor,  EntityManagerInterface $em)
    {
        $this->documentManager = $documentManager;
        $this->pdfExtractor = $pdfExtractor;
        $this->em = $em;
    }
    
    #[Route(name: 'app_document_index', methods: ['GET'])]
    public function index(DocumentRepository $documentRepository): Response
    {
        return $this->render('document/index.html.twig', [
            'documents' => $documentRepository->findAll(),
        ]);
    }
    
    #[Route('/new', name: 'app_document_new', methods: ['GET', 'POST'])]
public function new(Request $request): Response
{
    $document = new Document();
    $documentForm = $this->createForm(DocumentType::class, $document);
    $documentForm->handleRequest($request);

    if ($documentForm->isSubmitted() && $documentForm->isValid()) {

        /** @var UploadedFile|null $file */
        $file = $documentForm->get('file')->getData();

        if (!$file) {
            $this->addFlash('danger', 'Aucun fichier fourni.');
            return $this->redirectToRoute('app_document_new');
        }

        // Champs Intervention (hors formulaire Symfony)
        $interventionData = [
            'reference' => $request->request->get('reference'),
            'clientNom' => $request->request->get('clientNom'),
            'adresse' => $request->request->get('adresse'),
            'demande' => $request->request->get('demande'),
            'detail' => $request->request->get('detail'),
        ];

        // 1️ Upload du fichier
        $this->documentManager->uploadFile($document, $file);

        // 2️ Lien avec l’intervention
        $this->documentManager->attachToIntervention($document, $interventionData);

        // 3️ Flush UNIQUE
        $this->em->persist($document);
        $this->em->flush();

        $this->addFlash('success', 'Document créé et lié à l’intervention.');

        return $this->redirectToRoute('app_document_show', [
            'id' => $document->getId()
        ]);
    }

    return $this->render('document/new.html.twig', [
        'documentForm' => $documentForm->createView(),
    ]);
}
    //#[Route('/new', name: 'app_document_new', methods: ['GET', 'POST'])]
   /* public function new(Request $request, EntityManagerInterface $em): Response
    {
        $document = new Document();
        $documentForm = $this->createForm(DocumentType::class, $document);

        $intervention = new Intervention();  //??
        $interventionForm = $this->createForm(InterventionType::class, $intervention); //??

        $documentForm->handleRequest($request);
        $interventionForm->handleRequest($request);  //??

        /**
         * ---------------------------------------
         * CAS A : création classique d’un document
         * ---------------------------------------
         */
       /* if ($documentForm->isSubmitted() && $documentForm->isValid()) {

            /** @var UploadedFile|null $file */
      //      $file = $documentForm->get('file')->getData();

          //  if ($file) {
                // Utilisation du DocumentManager
         //       $document = $this->documentManager->createDocument($file);

          //      $em->flush();

           //     $this->addFlash('success', 'Document créé.');
           //     return $this->redirectToRoute('app_document_show', [
           //         'id' => $document->getId()
          //      ]);
          //  }
        /*          if (!$file) {
            $this->addFlash('danger', 'Aucun fichier fourni.');
            return $this->redirectToRoute('app_document_new');
        }


       //     $this->addFlash('danger', 'Aucun fichier fourni.');
       // }
         // Récupération de la référence chantier (champ du formulaire ou input séparé)
        $reference = $request->request->get('reference');

        if (!$reference) {
            $this->addFlash('danger', 'Référence chantier obligatoire.');
            return $this->redirectToRoute('app_document_new');
        }

        // Cherche une intervention existante
        $intervention = $this->em->getRepository(Intervention::class)
            ->findOneBy(['reference' => $reference]);

        if (!$intervention) {
            // Crée nouvelle intervention
            $intervention = new Intervention();
            $intervention->setReference($reference);
            $intervention->setClientNom($request->request->get('clientNom', ''));
            $intervention->setAdresse($request->request->get('adresse', ''));
            $this->em->persist($intervention);
        }

        // Création du document lié à l'intervention
        $document = $this->documentManager->createDocument($file, $intervention);

        $this->em->flush();

        $this->addFlash('success', 'Document créé et lié à l’intervention.');

        return $this->redirectToRoute('app_document_show', ['id' => $document->getId()]);
    }

    return $this->render('document/new.html.twig', [
        'documentForm' => $documentForm->createView(),
    ]);*/


        /**
         * ----------------------------------------------------
         * CAS B : Création d'une Intervention + upload AJAX
         * ----------------------------------------------------
         */
       /* if ($interventionForm->isSubmitted() && $interventionForm->isValid()) {

            $documentId = $request->request->get('document_id');
            $document = $documentId ? $em->getRepository(Document::class)->find($documentId) : null;

            if ($document) {
                $intervention->addDocument($document);
                $document->setIntervention($intervention);
            }

            // Récupération éventuelle (AJAX)
            if ($clientNom = $request->request->get('clientNom')) {
                $intervention->setClientNom($clientNom);
            }
            if ($adresse = $request->request->get('adresse')) {
                $intervention->setAdresse($adresse);
            }
            if ($demande = $request->request->get('demande')) {
                $intervention->setDemande($demande);
            }
            if ($detail = $request->request->get('detail')) {
                $intervention->setDetail($detail);
            }

            $em->persist($intervention);
            $em->flush();

            $this->addFlash('success', 'Intervention créée et document lié.');
            return $this->redirectToRoute('app_document_index');
        }

        return $this->render('document/new.html.twig', [
            'documentForm' => $documentForm->createView(),
            'interventionForm' => $interventionForm->createView(),
            'document' => $document,
        ]);
    }*/

    // Upload AJAX et retour JSON pour l’iframe
   #[Route('/upload', name: 'app_document_upload', methods: ['POST'])]
public function uploadJson(Request $request): JsonResponse
{
    $document = new Document();
    $form = $this->createForm(DocumentType::class, $document);
    $form->handleRequest($request);

    if (!$form->isSubmitted() || !$form->isValid()) {
        return new JsonResponse([
            'status' => 'error',
            'errors' => (string) $form->getErrors(true, false),
        ], 400);
    }

    /** @var UploadedFile|null $file */
    $file = $form->get('file')->getData();
    if (!$file) {
        return new JsonResponse([
            'status' => 'error',
            'message' => 'Aucun fichier reçu.',
        ], 400);
    }

    // Récupération des champs Intervention
    $interventionData = [
        'reference' => $request->request->get('reference'),
        'clientNom'  => $request->request->get('clientNom'),
        'adresse'    => $request->request->get('adresse'),
        'demande'    => $request->request->get('demande'),
        'detail'     => $request->request->get('detail'),
    ];

    try {
        // Création du document lié à l'intervention
       // $document = $this->documentManager->createDocumentWithIntervention($file, $interventionData);
       $this->documentManager->uploadFile($document, $file);

        $this->em->flush();
    }
    catch (\Exception $e) {
        return new JsonResponse([
            'status' => 'error',
            'message' => 'Erreur upload : ' . $e->getMessage(),
        ], 500);
    }

    return new JsonResponse([
        'status' => 'success',
        'id' => $document->getId(),
        'filename' => $document->getFilename(),
        'path' => '/uploads/documents/' . $document->getFilename(),
    ]);
}
    #[Route('/{id}', name: 'app_document_show', methods: ['GET'])]
    public function show(Document $document): Response
    {
        return $this->render('document/show.html.twig', ['document' => $document]);
    }

    #[Route('/{id}/edit', name: 'app_document_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Document $document, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(DocumentType::class, $document, [
            'is_edit' => true,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Document mis à jour ');
            return $this->redirectToRoute('app_document_show', ['id' => $document->getId()]);
        }

        return $this->render('document/edit.html.twig', [
            'document' => $document,
            'form' => $form->createView(),
             'intervention' => $document->getIntervention(), // peut être null
        ]);
    }

    #[Route('/{id}', name: 'app_document_delete', methods: ['POST'])]
    public function delete(Request $request, Document $document, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $document->getId(), $request->request->get('_token'))) {
            $em->remove($document);
            $em->flush();
        }
        return $this->redirectToRoute('app_document_index');
    }
}
