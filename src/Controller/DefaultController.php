<?php

namespace App\Controller;

use App\Entity\SearchEvent;
use App\Form\SearchEventType;
use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function home(EventRepository $eventRepository)
    {
        $searchEvent = new SearchEvent();
        $searchForm = $this->createForm(SearchEventType::class, $searchEvent);
        $events = $eventRepository->searchEvents();

        return $this->render('default/home.html.twig', [
            "events" => $events,
            "searchForm" => $searchForm->createView()
        ]);
    }
}
