<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\EventCancelation;
use App\Entity\EventState;
use App\Form\EventCancelationType;
use App\Form\EventType;
use App\Form\LocationType;
use App\Repository\EventStateRepository;
use App\Repository\LocationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/sorties", name="event_")
 */
class EventController extends AbstractController
{
    /**
     * @Route("/annuler/{id}", name="cancel")
     */
    public function cancel(
        Event $event,
        EventStateRepository $eventStateRepository,
        EntityManagerInterface $entityManager,
        Request $request
    )
    {
        //vérifie que c'est bien l'organisateur ou un admin !
        //voir le Security\Voter\EventVoter
        $this->denyAccessUnlessGranted('cancel', $event);

        $eventCancelation = new EventCancelation();
        $form = $this->createForm(EventCancelationType::class, $eventCancelation);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $eventCancelation->setEvent($event);
            $eventCancelation->setDateCanceled(new \DateTime());

            //récupère le bon état depuis la bdd
            $canceledState =  $eventStateRepository->findOneBy(['name' => EventState::CANCELED]);

            //on l'hydrate dans notre sortie
            $event->setState($canceledState);
            $event->setDateUpdated(new \DateTime());

            //on sauvegarde
            $entityManager->persist($event);
            $entityManager->persist($eventCancelation);
            $entityManager->flush();

            //petit message à afficher et redirection à tous les coups
            $this->addFlash('success', 'La sortie a été annulée !');
            return $this->redirectToRoute('event_detail', ['id' => $event->getId()]);
        }

        return $this->render('event/cancel.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/details/{id}", name="detail")
     */
    public function detail(Event $event)
    {
        //utilise le EventVoter pour être sûr qu'on peut consulter cette sortie
        $this->denyAccessUnlessGranted('view', $event);

        return $this->render('event/detail.html.twig', [
            "event" => $event
        ]);
    }

    /**
     * @Route("/creer", name="create")
     */
    public function create(Request $request, EventStateRepository $eventStateRepository, LocationRepository $locationRepository)
    {
        $event = new Event();
        $event->setDateCreated(new \DateTime());
        $event->setCampus($this->getUser()->getCampus());
        $state = $eventStateRepository->findOneBy(['name' => EventState::OPEN]);
        $event->setState($state);
        $eventForm = $this->createForm(EventType::class, $event);
        $eventForm->handleRequest($request);

        if ($eventForm->isSubmitted()){
            $locationId = $request->request->get('event')['location'];
            $location = $locationRepository->find($locationId);
            $event->setLocation($location);
        }

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
