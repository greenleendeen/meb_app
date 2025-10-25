<?php

namespace App\Controller;

use App\Repository\InterventionRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class CalendarController extends AbstractController
{
    #[Route('/calendar', name: 'app_calendar')]
    public function index(UserRepository $userRepo): Response
    {
        // Récupère les techniciens avec rôle TECHNICIEN (stocké dans le champ JSON "roles")
       $allUsers = $userRepo->findAll();

    // Ne garder que les techniciens
    $techniciens = array_filter($allUsers, function($u) {
        return in_array('ROLE_TECHNICIEN', $u->getRoles());
    });

    return $this->render('calendar/index.html.twig', [
        'techniciens' => $techniciens,
        ]);
    }

    #[Route('/calendar/events', name: 'app_calendar_events', methods: ['GET'])]
    public function events(Request $request, InterventionRepository $interventionRepo): JsonResponse
    {
        $start = $request->query->get('start');
        $end = $request->query->get('end');
        $technicienId = $request->query->get('technicien');

        if (!$start || !$end) {
            return $this->json([]);
        }

        try {
            $start = new \DateTime($start);
            $end   = new \DateTime($end);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Invalid dates'], 400);
        }

        $qb = $interventionRepo->createQueryBuilder('i')
            ->where('i.dateIntervention BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end);

        if ($technicienId) {
            $qb->andWhere('i.technicien = :tech')
                ->setParameter('tech', $technicienId);
        }

        $interventions = $qb->getQuery()->getResult();

        $events = array_map(fn($i) => [
            'id'    => $i->getId(),
            'title' => $i->getClientNom() ?? 'Intervention',
            'start' => $i->getDateIntervention()->format('Y-m-d') . 'T' . $i->getHeureDebut()->format('H:i:s'),
            'end'   => $i->getDateIntervention()->format('Y-m-d') . 'T' . $i->getHeureFin()->format('H:i:s'),
        ], $interventions);

        return $this->json($events);
    }
}
