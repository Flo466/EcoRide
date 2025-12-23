<?php

namespace App\Repository;

use App\Entity\Review;
use App\Enum\ReviewStatus; // N'oublie pas d'importer ton Enum de statut
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Review>
 */
class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    /**
     * Récupère les avis laissés POUR un utilisateur donné (cible/conducteur).
     * Inclut les détails de l'utilisateur qui a écrit l'avis (l'auteur).
     *
     * @param int $reviewedUserId L'ID de l'utilisateur qui reçoit les avis (le conducteur).
     * @param ReviewStatus|null $status Le statut des avis à récupérer (ex: APPROVED). Si null, récupère tous les statuts.
     * @return Review[] Returns an array of Review objects
     */
    public function findReviewsForReviewedUser(int $reviewedUserId, ?ReviewStatus $status = null): array
    {
        $qb = $this->createQueryBuilder('r')
            // Filtre les avis où 'reviewedUser' est l'utilisateur cible
            ->andWhere('r.reviewedUser = :reviewedUserId')
            ->setParameter('reviewedUserId', $reviewedUserId)
            // Jointure pour récupérer les détails de l'utilisateur qui a écrit l'avis (l'auteur)
            ->leftJoin('r.user', 'author')
            ->addSelect('author') // Sélectionne l'entité 'author' (user) pour la sérialisation
            ->orderBy('r.createdAt', 'DESC');

        // Ajoute le filtre par statut si un statut est spécifié
        if ($status !== null) {
            $qb->andWhere('r.status = :status')
                ->setParameter('status', $status);
        }

        return $qb->getQuery()->getResult();
    }

    // Tu peux garder ou supprimer les méthodes commentées si tu ne les utilises pas.
    //    /**
    //     * @return Review[] Returns an array of Review objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Review
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
