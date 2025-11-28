<?php

namespace App\Controller;

use App\Repository\InterventionRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;


final class CalendarController extends AbstractController
{
    #[Route('/calendar', name: 'app_calendar')]
    public function index(UserRepository $userRepo): Response
    {
    // Récupère uniquement les techniciens grâce à ta méthode personnalisée
    $techniciens = $userRepo->findByRole('ROLE_TECHNICIEN');

    return $this->render('calendar/index.html.twig', [
        'techniciens' => $techniciens,
    ]);
    }

    #[Route('/calendar/events', name: 'app_calendar_events', methods: ['GET'])]
    public function events(Request $request, InterventionRepository $interventionRepo): JsonResponse
    {
        // Affiche les paramètres envoyés par FullCalendar
        dump($request->query->all());

        $start = $request->query->get('start');
        $end = $request->query->get('end');
        $technicienId = $request->query->get('tech'); // correspond au param envoyé par JS
        if (!$start || !$end) {
            return $this->json([]);
        }

        try {
            $start = new \DateTimeImmutable(str_replace(' ', '+', $request->query->get('start')));
            $end   = new \DateTimeImmutable(str_replace(' ', '+', $request->query->get('end')));
        } catch (\Exception $e) {
            return $this->json(['error' => 'Invalid dates'], 400);
        }


        $qb = $interventionRepo->createQueryBuilder('i')
            ->where('i.dateIntervention BETWEEN :start AND :end')
            ->setParameter('start', $start->format('Y-m-d H:i:s'))
            ->setParameter('end', $end->format('Y-m-d H:i:s'));

        if ($technicienId) {
            $qb->andWhere('i.technicien = :tech')
                ->setParameter('tech', $technicienId);
        }
        $interventions = $qb->getQuery()->getResult();

   // Ici, on gère la couleur du technicien
    $events = [];
    foreach ($interventions as $intervention) {
        $technicien = $intervention->getTechnicien(); // ou getUser() selon ton entité

       $events[] = [
    'id' => $intervention->getId(),
    'title' => $intervention->getClientNom() ?? 'Intervention', // le nom du client en titre
    'start' => $intervention->getDateIntervention()->format('Y-m-d') . 'T' . $intervention->getHeureDebut()->format('H:i:s'),
    'end' => $intervention->getDateIntervention()->format('Y-m-d') . 'T' . $intervention->getHeureFin()->format('H:i:s'),
    'backgroundColor' => $technicien?->getCouleur() ?? '#999999',
    'borderColor' => $technicien?->getCouleur() ?? '#999999',

     // **Important : resourceId pour que l'événement soit lié à la colonne du technicien**
            'resourceId' => $technicien?->getId(),

    //  extendedProps  pour eventDidMount (disponibles dans info.event.extendedProps)
    'extendedProps' => [
        'adresse' => $intervention->getAdresse(),
        'technicien' => $technicien?->getNom(),
        'color' => $technicien?->getCouleur(),
    ],
];
    }

    return $this->json($events);
}
//route /calendar/techniciens pour envoyer la liste
#[Route('/calendar/techniciens', name: 'calendar_techniciens')]
public function techniciens(UserRepository $userRepo): JsonResponse
{
    $techs = $userRepo->findByRole('ROLE_TECHNICIEN');

    $data = array_map(fn($t) => [
        'id' => $t->getId(),
        'nom' => $t->getNom(),
        'couleur' => $t->getCouleur(),
    ], $techs);

    return $this->json($data);
}
// ajouter la route backend de mise à jour du calendrier
#[Route('/calendar/update', name: 'app_calendar_update', methods: ['POST'])]
public function update(Request $request, InterventionRepository $repo, UserRepository $userRepo, EntityManagerInterface $em): JsonResponse
{
    $data = json_decode($request->getContent(), true);

    if (!$data || !isset($data['id'], $data['start'], $data['end'])) {
        return $this->json(['error' => 'Invalid data'], 400);
    }

    $intervention = $repo->find($data['id']);
    if (!$intervention) {
        return $this->json(['error' => 'Intervention not found'], 404);
    }

    try {
        $start = new \DateTime($data['start']);
        $end = new \DateTime($data['end']);
    } catch (\Exception $e) {
        return $this->json(['error' => 'Invalid date format'], 400);
    }

    $intervention->setDateIntervention($start);
    $intervention->setHeureDebut($start);
    $intervention->setHeureFin($end);

    // Changement de technicien (User)
    if (!empty($data['technicien'])) {
        $technicien = $userRepo->find($data['technicien']);
        if ($technicien) {
            $intervention->setTechnicien($technicien);
        }
    }
    $em->flush();

    return $this->json(['success' => true]);
}



      //  $events = array_map(fn($i) => [
       //     'id'    => $i->getId(),
      //      'title' => $i->getClientNom() ?? 'Intervention',
      //      'start' => $i->getDateIntervention()->format('Y-m-d') . 'T' . $i->getHeureDebut()->format('H:i:s'),
      //      'end'   => $i->getDateIntervention()->format('Y-m-d') . 'T' . $i->getHeureFin()->format('H:i:s'),
      //  ], $interventions);

     //   return $this->json($events);
  //  }
}
