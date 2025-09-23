<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SuperAdminController extends AbstractController
{
    #[Route('/super', name: 'super_dashboard')]
    public function index(): Response
  {
        /** @var User $user */
        $user = $this->getUser(); // récupère le super admin connecté

        return $this->render('super_admin/index.html.twig', [
            'user' => $user,
        ]);
    }
}
