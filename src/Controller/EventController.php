<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\EventState;
use App\Form\EventType;
use App\Form\LocationType;
use App\Repository\EventStateRepository;
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
    public function cancel(Event $event, EventStateRepository $eventStateRepository, EntityManagerInterface $entityManager)
    {
        //vérifie que c'est bien l'organisateur ou un admin !
        //voir le Security\Voter\EventVoter
        $this->denyAccessUnlessGranted('cancel', $event);

        //récupère le bon état depuis la bdd
        $canceledState =  $eventStateRepository->findOneBy(['name' => EventState::CANCELED]);

        //on l'hydrate dans notre sortie
        $event->setState($canceledState);
        $event->setDateUpdated(new \DateTime());

        //on sauvegarde
        $entityManager->persist($event);
        $entityManager->flush();

        //petit message à afficher et redirection à tous les coups
        $this->addFlash('success', 'La sortie a été annulée !');
        return $this->redirectToRoute('event_detail', ['id' => $event->getId()]);
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
