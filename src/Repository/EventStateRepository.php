<?php

namespace App\Repository;

use App\Entity\EventState;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EventState|null find($id, $lockMode = null, $lockVersion = null)
 * @method EventState|null findOneBy(array $criteria, array $orderBy = null)
 * @method EventState[]    findAll()
 * @method EventState[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventStateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventState::class);
    }

    /**
     * @param array $stateNames
     * @return int|mixed|string
     */
    public function findStatesBetween(array $stateNames)
    {
        $qb = $this->createQueryBuilder('s');
        foreach($stateNames as $stateName){
            $qb->orWhere('s.name = :name')->setParameter(':name', $stateName);
        }
        return $qb->getQuery()->getResult();
    }

    // /**
    //  * @return EventState[] Returns an array of EventState objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?EventState
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
