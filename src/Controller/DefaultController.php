<?php

namespace App\Controller;

use App\Entity\SearchEvent;
use App\Form\SearchEventType;
use App\Repository\EventRepository;
use App\Yo;
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
        $yo = new Yo();

        $searchEvent = new SearchEvent();
        $searchEvent->setCampus($this->getUser()->getCampus());
        $searchForm = $this->createForm(SearchEventType::class, $searchEvent);
        $searchForm->handleRequest($request);
        $events = $eventRepository->searchEvents($searchEvent, $this->getUser());

        return $this->render('default/home.html.twig', [
            "events" => $events,
            "searchForm" => $searchForm->createView()
        ]);
    }
}
