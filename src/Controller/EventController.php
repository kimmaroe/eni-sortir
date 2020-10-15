<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\EventState;
use App\Form\EventType;
use App\Form\LocationType;
use App\Form\LoginFormType;
use App\Repository\EventStateRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/sorties", name="event_")
 */
class EventController extends AbstractController
{
    /**
     * @Route("/details/{id}", name="detail")
     */
    public function detail(Event $event)
    {
        return $this->render('event/detail.html.twig', [
            "event" => $event
        ]);
    }

    /**
     * @Route("/creer", name="create")
     */
    public function create(Request $request, EventStateRepository $eventStateRepository)
    {
        $event = new Event();
        $event->setDateCreated(new \DateTime());
        $event->setCampus($this->getUser()->getCampus());
        $state = $eventStateRepository->findOneBy(['name' => EventState::OPEN]);
        $event->setState($state);
        $eventForm = $this->createForm(EventType::class, $event);
        $eventForm->handleRequest($request);

        if ($eventForm->isSubmitted() && $eventForm->isValid()){
            $em = $this->getDoctrine()->getManager();
            $em->persist($event);
            $em->flush();

            $this->addFlash('success', 'Votre sortie a bien été créée !');
            return $this->redirectToRoute('event_detail', ['id' => $event->getId()]);
        }

        $locationForm = $this->createForm(LocationType::class);

        return $this->render('event/create.html.twig', [
            "event" => $event,
            "eventForm" => $eventForm->createView(),
            "locationForm" => $locationForm->createView()
        ]);
    }
}
