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

    //    /**
    //     * @return Carpooling[] Returns an array of Carpooling objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Carpooling
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    /**
     * Filtrage dynamique des covoiturages
     */
    public function findByFilter(array $criteria, ?\DateTimeImmutable $departureAt, ?int $seatsAvaible, ?float $price): array
    {
        $qb = $this->createQueryBuilder('c');

        // Filtres basés sur $criteria
        foreach ($criteria as $field => $value) {
            $qb->andWhere("c.$field LIKE :$field")
                ->setParameter($field, "%$value%");
        }

        if ($departureAt) {
            $qb->andWhere('c.deparatureAt >= :departureAt')
                ->setParameter('departureAt', $departureAt);
        }

        if ($seatsAvaible !== null) {
            $qb->andWhere('c.seatsAvaible >= :seats')
                ->setParameter('seats', $seatsAvaible);
        }

        if ($price !== null) {
            $qb->andWhere('c.price <= :price')
                ->setParameter('price', $price);
        }

        return $qb->orderBy('c.deparatureAt', 'ASC')->getQuery()->getResult();
    }
}
