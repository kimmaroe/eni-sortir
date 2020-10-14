<?php

namespace App\Repository;

use App\Entity\SearchEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SearchEvent|null find($id, $lockMode = null, $lockVersion = null)
 * @method SearchEvent|null findOneBy(array $criteria, array $orderBy = null)
 * @method SearchEvent[]    findAll()
 * @method SearchEvent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SearchEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SearchEvent::class);
    }

    // /**
    //  * @return SearchEvent[] Returns an array of SearchEvent objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SearchEvent
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
