<?php

namespace App\Repository;

use App\Entity\Intervention;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Intervention>
 */
class InterventionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Intervention::class);
    }

/**
     * Recherche dynamique d'interventions selon $criteria
     *
     * $criteria possible keys:
     *  - 'reference' (exact)
     *  - 'q' (texte libre -> clientNom, adresse, demande, extracted_text des documents)
     *  - 'adresse' (contains)
     *  - 'technicien' (user id)
     *  - 'docType' (Document.type enum)
     *  - 'docStatus' (Document.status si présent)
     *  - 'dateFrom' / 'dateTo' (format Y-m-d)
     */
    public function search(array $criteria, int $limit = 15, int $offset = 0): array
    {
        $qb = $this->createQueryBuilder('i')
            ->leftJoin('i.documents', 'd')
            ->leftJoin('i.technicien', 't')
            ->addSelect('d', 't')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        // Référence exacte
        if (!empty($criteria['reference'])) {
            $qb->andWhere('i.reference = :ref')
                ->setParameter('ref', $criteria['reference']);
        }

        // Adresse "contient"
        if (!empty($criteria['adresse'])) {
            $qb->andWhere('i.adresse LIKE :addr')
                ->setParameter('addr', '%' . $criteria['adresse'] . '%');
        }

        // Technicien
if (!empty($criteria['technicien'])) {
    $qb->andWhere('i.technicien = :tech')
       ->setParameter('tech', $criteria['technicien']); // Doctrine convertira l'id en entité
}

        // Type de document
        if (!empty($criteria['typeDocument'])) {
            $qb->andWhere('d.type = :typeDoc')
                ->setParameter('typeDoc', $criteria['typeDocument']);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Compte les résultats pour la pagination
     */
    public function countSearchResults(array $criteria): int
    {
        $qb = $this->createQueryBuilder('i')
            ->select('COUNT(DISTINCT i.id)')
            ->leftJoin('i.documents', 'd')
            ->leftJoin('i.technicien', 't');

        if (!empty($criteria['reference'])) {
            $qb->andWhere('i.reference = :ref')
                ->setParameter('ref', $criteria['reference']);
        }

        if (!empty($criteria['adresse'])) {
            $qb->andWhere('i.adresse LIKE :addr')
                ->setParameter('addr', '%' . $criteria['adresse'] . '%');
        }

if (!empty($criteria['technicien'])) {
    $qb->andWhere('i.technicien = :tech')
        ->setParameter('tech', $criteria['technicien']);
}

        if (!empty($criteria['typeDocument'])) {
            $qb->andWhere('d.type = :typeDoc')
                ->setParameter('typeDoc', $criteria['typeDocument']);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }



    //    /**
    //     * @return Intervention[] Returns an array of Intervention objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('i.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Intervention
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
