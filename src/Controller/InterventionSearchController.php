<?php

namespace App\Controller;

use App\Form\SearchInterventionType;
use App\Repository\InterventionRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InterventionSearchController extends AbstractController
{
    #[Route('/interventions/search', name: 'app_intervention_search', methods: ['GET', 'POST'])]
    public function index(Request $request, InterventionRepository $repo, UserRepository $userRepo): Response
    {
        // 1) Récupère la liste des techniciens (ROLE_TECHNICIEN)
        $techniciens = $userRepo->findByRole('ROLE_TECHNICIEN');

        // 2) Crée le formulaire de recherche
        $form = $this->createForm(SearchInterventionType::class, null, [
            'method' => 'GET',
    'techniciens' => $techniciens,
]);

$form->handleRequest($request);
$criteria = $form->isSubmitted() && $form->isValid()
    ? $form->getData()
    : [];

// --- AJOUT POUR LA RECHERCHE RAPIDE ---
$quickRef = $request->query->get('reference');
if ($quickRef) {
    $criteria['reference'] = $quickRef;
}


$criteria = $form->getData() ?? [];

        // 3) Récupère les critères envoyés
        $criteria = $form->isSubmitted() && $form->isValid()
            ? $form->getData()
            : [];
        //4// Convertir technicien id → objet User
            if (!empty($criteria['technicien'])) {
    $criteria['technicien'] = $userRepo->find($criteria['technicien']);
}
        // 5) Gestion pagination
        $page   = max(1, (int) $request->query->get('page', 1));
        $limit  = 15;
        $offset = ($page - 1) * $limit;

        // 6) Exécute la recherche
        $results = $repo->search($criteria, $limit, $offset);
        $total   = $repo->countSearchResults($criteria);
        $pages   = (int) ceil($total / $limit);

        // 7) Affiche la page
        return $this->render('intervention/search.html.twig', [
            'form'        => $form->createView(),
            'results'     => $results,
            'criteria'    => $criteria,
            'page'        => $page,
            'pages'       => $pages,
            'total'       => $total,
            'techniciens' => $techniciens,
        ]);
    }
}
