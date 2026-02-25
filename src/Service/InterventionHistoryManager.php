<?php

namespace App\Service;

use App\Entity\Intervention;
use App\Entity\InterventionHistory;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class InterventionHistoryManager
{
    public function __construct(private EntityManagerInterface $em) {}

    /**
     * Crée des historiques pour les champs modifiés
     */
    public function trackChanges(Intervention $intervention, ?User $user = null, array $fieldsToCheck = []): void
    {
        // Par défaut, tous les champs importants
        if (empty($fieldsToCheck)) {
            $fieldsToCheck = ['clientNom', 'adresse', 'demande', 'detail', 'dateIntervention', 'heureDebut', 'heureFin', 'technicien'];
        }

        $uow = $this->em->getUnitOfWork();
        $meta = $this->em->getClassMetadata(Intervention::class);
        $changeSet = $uow->getEntityChangeSet($intervention);


        foreach ($fieldsToCheck as $field) {
            if (isset($changeSet[$field])) {
                [$old, $new] = $changeSet[$field];

                $history = new InterventionHistory();
                $history->setIntervention($intervention);
                $history->setFiledName($field);
                $history->setOldValue((string) $old);
                $history->setNewValue((string) $new);
                $history->setActionType('update');
                $history->setModifiedBy($user);

                $this->em->persist($history);
            }
        }
    }

    
}
