<?php

namespace App\Repository;

use App\Entity\Carpooling;
use App\Entity\User; // Import the User entity
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
    public function findBySearchCriteria(?string $departurePlace, ?string $arrivalPlace, ?\DateTimeInterface $departureDate): array
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

    /**
     * Finds all carpoolings associated with a specific user, either as a driver or a passenger.
     *
     * @param User $user The user entity to search for.
     * @return Carpooling[] Returns an array of Carpooling objects.
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('c')
            ->join('c.carpoolingUsers', 'cu') // Join with the CarpoolingUser entity
            ->where('cu.user = :user')       // Filter by the user associated with CarpoolingUser
            ->setParameter('user', $user)
            ->orderBy('c.departureDate', 'ASC') // Order by departure date for a logical list
            ->addOrderBy('c.departureTime', 'ASC') // Then by departure time
            ->getQuery()
            ->getResult();
    }
}
