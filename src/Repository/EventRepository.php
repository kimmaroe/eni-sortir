<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\Registration;
use App\Entity\SearchEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function searchEvents(SearchEvent $searchData, UserInterface $currentUser)
    {
        $qb = $this->createQueryBuilder('e')
            ->leftJoin('e.registrations', 'r')->addSelect('r')
            ->join('e.creator', 'c')->addSelect('c')
            ->join('e.campus', 'camp')->addSelect('camp')
            ->join('e.state', 's')->addSelect('s');

        //campus
        if ($searchData->getCampus()){
            $qb->andWhere('e.campus = :campus')->setParameter('campus', $searchData->getCampus());
        }

        //inclure les événements que j'ai créés
        if ($searchData->getIncludeCreatedEvent()){
            $qb->andWhere('e.creator = :me')->setParameter('me', $currentUser);
        }

        //inclure les événements auxquels je suis inscrit
        if ($searchData->getIncludeRegistered()){
            $qb->andWhere('r.user = :me')->setParameter('me', $currentUser);
        }

        //inclure les événements auxquels je ne suis PAS inscrit
        //l'air de rien, c'est la méga galère ce truc (en tout cas pour moi)
        if ($searchData->getIncludeNotRegistered()){
            //crée un nouveau querybuilder dans une autre table
            $subqb = $this->getEntityManager()->createQueryBuilder()->from(Registration::class, 'r');
            $subqb->select('r');
            $subqb->andWhere('r.user != :me')->setParameter('me', $currentUser);
            $subResults = $subqb->getQuery()->getResult();
            dump($subResults);
            //$qb->andWhere('r.user = :me')->setParameter('me', $currentUser);
        }

        //mots clés
        if ($searchData->getKeyword()){
            //on sépare tous les mots de la phrase tapée
            $eachWords = explode(" ", $searchData->getKeyword());
            //pour chaque mot, on va faire un OR WHERE qu'on va ajouter là-dedans
            $orStatements = $qb->expr()->orX();
            foreach ($eachWords as $keyword) {
                //on ajoute une clause WHERE LIKE sur le titre
                $orStatements->add(
                    $qb->expr()->like('e.title', $qb->expr()->literal('%' . $keyword . '%'))
                );
            }
            //finalement, on ajoute ces multi OR à notre requête
            $qb->andWhere($orStatements);
        }

        //date de début
        $startAt = $searchData->getDateStart() ? $searchData->getDateStart() : new \DateTime();
        $qb->andWhere('e.dateStart >= :now')->setParameter('now', $startAt);

        //date de fin
        if ($searchData->getDateEnd()){
            $qb->andWhere('e.dateStart >= :now')->setParameter('now', $searchData->getDateEnd());
        }

        $qb->setMaxResults(20);
        $query = $qb->getQuery();
        $results = $query->getResult();
        return $results;
    }

    // /**
    //  * @return Event[] Returns an array of Event objects
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
    public function findOneBySomeField($value): ?Event
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
