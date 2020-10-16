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

        //si une date de début est précisée, on va dire que c'est plus fort que la checkbox "inclure les év. passés"
        if ($searchData->getDateStart()){
            $qb->andWhere('e.dateStart >= :ds')->setParameter('ds', $searchData->getDateStart());
        }

        //date de fin
        if ($searchData->getDateEnd()){
            //on ajoute les heures pour que ce soit vraiment plus OU ÉGAL
            $dateMax = $searchData->getDateEnd()->setTime(23,59,59);
            $qb->andWhere('e.dateStart <= :de')->setParameter('de', $dateMax);
        }


        //les checkbox maintenant...
        //je les considère comme des OR à ajouter à la requête des champs de gauche
        //c'est pas logique dans tous les cas ce form

        $orExpression = $qb->expr()->orX();

        //inclure les événements auxquels je suis inscrit
        if ($searchData->getIncludeRegistered()){
            $orExpression->add(
                $qb->expr()->andX('r.user = :me')
            );
        }

        //inclure les événements auxquels je ne suis PAS inscrit
        //l'air de rien, c'est la méga galère ce truc (en tout cas pour moi)
        if ($searchData->getIncludeNotRegistered()){
            //crée un nouveau querybuilder pour faire une sous-requête
            //me retourne tous les IDs des événements auxquels je suis inscrit
            $subqb = $this->createQueryBuilder('ev')->select('ev.id');
            $subqb->leftJoin('ev.registrations', 'reg');
            $subqb->andWhere('reg.user = :me')->setParameter('me', $currentUser);
            $orExpression->add(
                $qb->expr()->andX($qb->expr()->notIn('e.id', $subqb->getDQL()))
            );
        }

        $qb->andWhere($orExpression);
        $qb->setParameter('me', $currentUser);
        //@todo : paginer les résultats

        $qb->setMaxResults(20);
        $qb->addOrderBy('e.dateRegistrationEnded', 'ASC');
        $query = $qb->getQuery();
        //dd($query);
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
