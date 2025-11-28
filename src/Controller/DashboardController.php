<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserRepository;
use App\Repository\InterventionRepository;

class DashboardController extends AbstractController
{
// DashboardController.php
#[Route('/dashboard', name: 'app_dashboard')]
public function dashboard(UserRepository $userRepo, InterventionRepository $interRepo): Response
{
    // Tous les techniciens
    $techniciens = $userRepo->findByRole('ROLE_TECHNICIEN');

    $today = new \DateTime();

    // Préparer un tableau structuré pour Twig
    $techData = [];

    foreach ($techniciens as $tech) {
        $interventions = $interRepo->findBy([
            'technicien' => $tech,
            'dateIntervention' => $today ,  // objet OK,
           
        ]);
$nbInterventions = count($interventions);
        $techData[] = [
    'user' => $tech,
    'interventions' => $interventions,
    'nbInterventions' => $nbInterventions,
        ];
    }

    return $this->render('dashboard.html.twig', [
        'techData' => $techData,
    ]);
}

}
