<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\EventState;
use App\Entity\Registration;
use App\Entity\SearchEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;
use function Doctrine\ORM\QueryBuilder;

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

    public function searchEvents(SearchEvent $searchData, UserInterface $currentUser, int $page)
    {
        $qb = $this->createQueryBuilder('e')
            ->leftJoin('e.registrations', 'r')->addSelect('r')
            ->join('e.creator', 'c')->addSelect('c')
            ->join('e.campus', 'camp')->addSelect('camp')
            ->join('e.state', 's')->addSelect('s')
            ->leftJoin('e.cancelation', 'cancel')->addSelect('cancel');

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

        //nombre de résultats par page
        $numberOfResultsPerPage = 18;
        $qb->setMaxResults($numberOfResultsPerPage);

        //le nombre de premiers résultats à omettre
        $offset = ($numberOfResultsPerPage * $page) - $numberOfResultsPerPage;
        $qb->setFirstResult($offset);

        $qb->addOrderBy('e.dateRegistrationEnded', 'ASC');
        $query = $qb->getQuery();

        //donne des infos sur le nombre max de résultats et gère les relations correctement
        $paginator = new Paginator($query);

        //on ne peut retourner qu'une seule variable, alors je met les résultats et des infos de pagination
        //dans un tableau contenant tout ça
        $infos = [
            'events' => $paginator,
            'pagination' => [
                'currentPage' => $page,
                'perPage' => $numberOfResultsPerPage,
                'firstResultNumber' => $offset+1,
                'lastResultNumber' => ($page * $numberOfResultsPerPage) < $paginator->count() ? $offset+$numberOfResultsPerPage : $paginator->count(),
                //combien de résultats auraient été retournés si nous n'en avions pas limité le nombre
                'maxResultsCount' => $paginator->count(),
                'previousPage' => $page > 1 ? $page-1 : false,
                'nextPage' => ($page * $numberOfResultsPerPage) < $paginator->count() ? $page+1 : false,
            ]
        ];

        dump($infos);
        return $infos;
    }

    public function findOldEventsToArchive()
    {
        $qb = $this->createQueryBuilder('e');
        $qb
            ->join('e.state', 's')
            ->andWhere($qb->expr()->in('s.name', [EventState::COMPLETED, EventState::CANCELED]))
            ->andWhere('e.dateEnd <= :onemonthago')
            ->setParameter('onemonthago', new \DateTime("-1 month"));
        $result = $qb->getQuery()->getResult();
        return $result;
    }

    public function findEventsToClose()
    {
        //ici je le fais en DQL, je ne m'en sort pas avec le querybuilder :D

        //sélectionne les événements ouverts ayant une date de clôture passée OU ayant atteint le nombre max d'inscrits
        $dql = "SELECT e FROM App\Entity\Event e 
                JOIN e.state s 
                JOIN e.registrations r
                WHERE s.name = :openstate 
                GROUP BY e.id 
                HAVING (
                    COUNT(r) >= e.maxRegistrations 
                    OR 
                    e.dateRegistrationEnded <= :now
                )";

        $query = $this->getEntityManager()->createQuery($dql);
        $query ->setParameter('openstate', EventState::OPEN);
        $query ->setParameter('now', new \DateTime());

        $result = $query->getResult();
        return $result;
    }


    public function findEventsToReopen()
    {
        //sélectionne les événements fermées ayant, suite à une désincription par exemple, un nombre de places disponible
        //et dont la date de clôture est toujours dans le futur
        $dql = "SELECT e FROM App\Entity\Event e 
                JOIN e.state s 
                JOIN e.registrations r
                WHERE s.name = :closedstate 
                GROUP BY e.id 
                HAVING (
                    COUNT(r) < e.maxRegistrations 
                    AND 
                    e.dateRegistrationEnded > :now
                )";

        $query = $this->getEntityManager()->createQuery($dql);
        $query ->setParameter('closedstate', EventState::CLOSED);
        $query ->setParameter('now', new \DateTime());

        $result = $query->getResult();
        return $result;
    }

    public function findCancelledEvents()
    {
        $qb =
            $this->createQueryBuilder('e')
            ->join('e.state', 's')
            ->where('s.name = :canceledStateName')
            ->setParameter('canceledStateName', EventState::CANCELED);
        return $qb->getQuery()->getResult();
    }
}
