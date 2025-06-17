<?php

namespace App\Repository;

use App\Entity\Carpooling;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Carpooling>
 */
class CarpoolingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Carpooling::class);
    }

     /**
     * Search carpoolings by departure, destination and date
     */
    public function findBySearchCriteria(?string $departurePlace, ?string $arrivalPlace, ?\DateTime $departureDate): array
    {
        $qb = $this->createQueryBuilder('c');

        if ($departurePlace) {
            $qb->andWhere('c.departurePlace LIKE :departurePlace')
            ->setParameter('departurePlace', '%' . $departurePlace . '%');
        }

        if ($arrivalPlace) {
            $qb->andWhere('c.arrivalPlace LIKE :arrivalPlace')
            ->setParameter('arrivalPlace', '%' . $arrivalPlace . '%');
        }

        if ($departureDate) {
            $startOfDay = $departureDate->setTime(0, 0, 0);
            $endOfDay = $departureDate->setTime(23, 59, 59);

            $qb->andWhere('c.departureDate BETWEEN :startOfDay AND :endOfDay')
            ->setParameter('startOfDay', $startOfDay)
            ->setParameter('endOfDay', $endOfDay);
        }

        return $qb->getQuery()->getResult();
    }

}
