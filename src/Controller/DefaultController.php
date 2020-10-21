<?php

namespace App\Controller;

use App\Entity\SearchEvent;
use App\Form\SearchEventType;
use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * Recherche et affichage des sorties, avec pagination !
     * @Route("/{page}", name="home", requirements={"page": "\d+"}, defaults={"page": 1})
     */
    public function home(EventRepository $eventRepository, Request $request, int $page = 1)
    {
        //entité entièrement bidon, non sauvegardée en bdd... juste pour aller avec le form
        $searchEvent = new SearchEvent();
        //on renseigne le campus du user par défaut
        $searchEvent->setCampus($this->getUser()->getCampus());

        $searchForm = $this->createForm(SearchEventType::class, $searchEvent);
        $searchForm->handleRequest($request);

        //requête perso, voir le repository
        //je passe mon entité bidon et le user au repository qui en a besoin
        $resultInfos = $eventRepository->searchEvents($searchEvent, $this->getUser(), $page);

        return $this->render('default/home.html.twig', [
            "events" => $resultInfos['events'],
            "pagination" => $resultInfos['pagination'],
            "searchForm" => $searchForm->createView(),
        ]);
    }
}
