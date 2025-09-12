<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/intervention', name: 'app_intervention_')]

final class InterventionController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('intervention/index.html.twig');
    }

    #[Route('/create', name: 'create')]

    public function create(Request $request): Response
    {
        //return $this->render('intervention/create.html.twig');
         $form = $this->createForm(\App\Form\InterventionType::class);

    return $this->render('intervention/create.html.twig', [
        'form' => $form->createView(),
    ]);
    }
}




