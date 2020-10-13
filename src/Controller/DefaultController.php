<?php

namespace App\Controller;

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
        $events = $eventRepository->searchEvents();

        return $this->render('default/home.html.twig', [
            "events" => $events
        ]);
    }
}
