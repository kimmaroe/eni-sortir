<?php

namespace App\Controller;

use App\Entity\Event;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/sorties", name="event_")
 */
class EventController extends AbstractController
{
    /**
     * @Route("/{id}", name="detail")
     */
    public function detail(Event $event)
    {
        return $this->render('event/detail.html.twig', [
            "event" => $event
        ]);
    }
}
