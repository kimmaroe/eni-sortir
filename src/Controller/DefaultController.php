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
     * @Route("/", name="home")
     */
    public function home(EventRepository $eventRepository, Request $request)
    {
        //entité entièrement bidon, non sauvegardée en bdd... juste pour aller avec le form
        $searchEvent = new SearchEvent();
        //on renseigne le campus du user par défaut
        $searchEvent->setCampus($this->getUser()->getCampus());

        $searchForm = $this->createForm(SearchEventType::class, $searchEvent);
        $searchForm->handleRequest($request);

        //requête perso, voir le repository
        $events = $eventRepository->searchEvents($searchEvent, $this->getUser());

        return $this->render('default/home.html.twig', [
            "events" => $events,
            "searchForm" => $searchForm->createView()
        ]);
    }
}
