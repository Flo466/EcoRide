<?php

namespace App\Repository;

use App\Entity\Car;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User; // N'oublie pas d'importer l'entité User pour le type-hinting

/**
 * @extends ServiceEntityRepository<Car>
 *
 * @method Car|null find($id, $lockMode = null, $lockVersion = null)
 * @method Car|null findOneBy(array $criteria, array $orderBy = null)
 * @method Car[]    findAll()
 * @method Car[]    findBy(array $criteria, array 
 * $orderBy = null, $limit = null, $offset = null)
 */
class CarRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Car::class);
    }

    /**
     * Récupère les véhicules d'un utilisateur avec la marque chargée (eager loaded).
     *
     * @param User $user L'utilisateur dont on veut récupérer les véhicules.
     * @return Car[]
     */
    public function findByUserWithBrand(User $user): array
    {
        return $this->createQueryBuilder('c') // 'c' est l'alias pour l'entité Car
            ->leftJoin('c.brand', 'b') // Jointure avec la relation 'brand' de Car, alias 'b'
            ->addSelect('b') // Sélectionne également les données de la marque
            ->where('c.user = :user') // Filtre par l'utilisateur
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }
}
