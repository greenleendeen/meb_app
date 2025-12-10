<?php

namespace App\Controller;

use App\Entity\Document;
use App\Entity\Intervention;
use App\Form\DocumentType;
use App\Form\InterventionType;
use App\Repository\DocumentRepository;
use App\Service\PdfExtractor;
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
    public function __construct(private PdfExtractor $pdfExtractor) {}

    #[Route(name: 'app_document_index', methods: ['GET'])]
    public function index(DocumentRepository $documentRepository): Response
    {
        return $this->render('document/index.html.twig', [
            'documents' => $documentRepository->findAll(),
        ]);
    }

   #[Route('/new', name: 'app_document_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $em): Response
{
    $document = new Document();
    $documentForm = $this->createForm(DocumentType::class, $document);
    $intervention = new Intervention();
    $interventionForm = $this->createForm(InterventionType::class, $intervention);

    $documentForm->handleRequest($request);
    $interventionForm->handleRequest($request);

    // CAS A: création d'un Document seul via form (non AJAX)
    if ($documentForm->isSubmitted() && $documentForm->isValid()) {
        /** @var UploadedFile $file */
        $file = $documentForm->get('file')->getData();
        if ($file) {
            $newFilename = uniqid() . '.' . $file->guessExtension();
            $uploadDir = $this->getParameter('documents_directory');
            $file->move($uploadDir, $newFilename);

            $document->setFilename($newFilename);
            $document->setPath('uploads/documents/' . $newFilename);

            $em->persist($document);
            $em->flush();

            $this->addFlash('success', 'Document créé.');
            return $this->redirectToRoute('app_document_show', ['id' => $document->getId()]);
        } else {
            $this->addFlash('danger', 'Aucun fichier fourni pour le document.');
        }
    }

    // CAS B: création d'une Intervention (form) qui réutilise un document uploadé via AJAX
    if ($interventionForm->isSubmitted() && $interventionForm->isValid()) {
        $documentId = $request->request->get('document_id'); // envoyé par ton JS après upload AJAX
        $document = null;
        if ($documentId) {
            $document = $em->getRepository(Document::class)->find($documentId);
        }

        if ($document) {
            $intervention->addDocument($document);
            $document->setIntervention($intervention);
        }

        // récupérer champs manuellement si tu veux surcharger via request (optionnel)
        $clientNom = $request->request->get('clientNom');
        if ($clientNom) $intervention->setClientNom($clientNom);
        $adresse = $request->request->get('adresse');
        if ($adresse) $intervention->setAdresse($adresse);
        $demande = $request->request->get('demande');
        if ($demande) $intervention->setDemande($demande);
        $detail = $request->request->get('detail');
        if ($detail) $intervention->setDetail($detail);

        $em->persist($intervention);
        $em->flush();

        $this->addFlash('success', 'Intervention créée et document lié ✅');
        return $this->redirectToRoute('app_document_index');
    }

    return $this->render('document/new.html.twig', [
        'documentForm' => $documentForm->createView(),
        'interventionForm' => $interventionForm->createView(),
        'document' => $document,
    ]);
}
    // Upload AJAX et retour JSON pour l’iframe
   #[Route('/upload', name: 'app_document_upload', methods: ['POST'])]
public function uploadJson(Request $request, EntityManagerInterface $em): JsonResponse
{
    $document = new Document();
    $form = $this->createForm(DocumentType::class, $document);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        /** @var UploadedFile $file */
        $file = $form->get('file')->getData();
        if ($file) {
            $newFilename = uniqid() . '.' . $file->guessExtension();
            $uploadDir = $this->getParameter('documents_directory');

            try {
                $file->move($uploadDir, $newFilename);
            } catch (\Exception $e) {
                return new JsonResponse(['status' => 'error', 'message' => 'Upload failed: '.$e->getMessage()], 500);
            }

            $document->setFilename($newFilename);
            $document->setPath('uploads/documents/' . $newFilename);

            $em->persist($document);
            $em->flush();

            return new JsonResponse([
                'status' => 'success',
                'id' => $document->getId(),
                'filename' => $document->getFilename(),
                'path' => $document->getPath(),
            ]);
        }
    }

    return new JsonResponse([
        'status' => 'error',
        'errors' => (string) $form->getErrors(true, false),
    ], 400);
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
