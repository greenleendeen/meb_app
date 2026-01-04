<?php

namespace App\Controller;

use App\Entity\CompteRendu;
use App\Entity\Document;
use App\Enum\DocumentType as DocumentEnum;
use App\Repository\InterventionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CompteRenduController extends AbstractController
{

// src/Controller/CompteRenduController.php

#[Route('/compte-rendu/add', name: 'compte_rendu_add', methods: ['POST'])]
public function add(Request $request, EntityManagerInterface $em, InterventionRepository $interventionRepo): Response
{
    $interventionId = $request->request->get('intervention_id');
    $intervention = $interventionRepo->find($interventionId);

    if (!$intervention) {
        throw $this->createNotFoundException('Intervention non trouvée.');
    }

    $description = $request->request->get('description');

    $compteRendu = new CompteRendu();
    $compteRendu->setIntervention($intervention);
    $compteRendu->setDescription($description);
    $compteRendu->setDateCreation(new \DateTimeImmutable());
    $compteRendu->setTechnicien($intervention->getTechnicien());

    // Gestion des fichiers uploadés
    $uploadedFiles = $request->files->get('documents', []);
    foreach ($uploadedFiles as $file) {
        if ($file) {
            $document = new Document();
            $filename = uniqid() . '.' . $file->guessExtension();
            $file->move($this->getParameter('documents_directory'), $filename);

            $document->setFile($file); // si tu stockes le fichier UploadedFile
            $document->setFilename($filename);
            $document->setType(DocumentEnum::COMPTE_RENDU); // ou autre logique si tu veux
            $document->setIntervention($intervention);
            $document->setCompteRendu($compteRendu);

            $em->persist($document);
        }
    }

    $em->persist($compteRendu);
    $em->flush();

    $this->addFlash('success', 'Compte rendu ajouté avec succès.');

    return $this->redirectToRoute('app_intervention_show', ['id' => $intervention->getId()]);
}


}