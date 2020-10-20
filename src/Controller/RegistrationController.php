<?php

namespace App\Controller;

use App\Entity\Registration;
use App\Repository\EventRepository;
use App\Repository\RegistrationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/inscriptions", name="registration_")
 */
class RegistrationController extends AbstractController
{
    /**
     * @Route("/{id}/ajouter", name="create")
     */
    public function create(
        int $id,
        EventRepository $eventRepository,
        RegistrationRepository $registrationRepository,
        EntityManagerInterface $entityManager
    )
    {
        $event = $eventRepository->find($id);

        if (!$event){
            $this->createNotFoundException("Oups ! Cette sortie n'existe plus !");
        }

        //voir le EventVoter (tchèque si places dispos et sortie ouverte)
        $this->denyAccessUnlessGranted('register', $event);

        //on tchèque si l'inscription existe déjà
        $foundRegistration = $registrationRepository->findOneBy(['user' => $this->getUser(), 'event' => $event]);
        if ($foundRegistration){
            $this->addFlash('error', "Vous êtes déjà inscrit à cette sortie !");
            return $this->redirectToRoute('event_detail', ['id' => $id]);
        }

        //crée l'inscription et l'hydrate
        $registration = new Registration();
        $registration->setUser($this->getUser());
        $registration->setEvent($event);
        $registration->setDateRegistered(new \DateTime());

        //sauvegarde en bdd
        $entityManager->persist($registration);
        $entityManager->flush();

        //on n'affiche rien, on redirige à tous coups
        $this->addFlash('success', "Vous êtes maintenant inscrit à cette sortie !");
        return $this->redirectToRoute('event_detail', ['id' => $id]);
    }

    /**
     * @Route("/{id}/retirer", name="remove")
     */
    public function remove(
        int $id,
        EventRepository $eventRepository,
        RegistrationRepository $registrationRepository,
        EntityManagerInterface $entityManager
    )
    {
        $event = $eventRepository->find($id);

        if (!$event){
            $this->createNotFoundException("Oups ! Cette sortie n'existe plus !");
        }

        //on tchèque si l'inscription existe déjà
        $foundRegistration = $registrationRepository->findOneBy(['user' => $this->getUser(), 'event' => $event]);
        if (!$foundRegistration){
            $this->addFlash('error', "Vous n'êtes pas inscrit à cette sortie !");
            return $this->redirectToRoute('event_detail', ['id' => $id]);
        }

        //supprime en bdd
        $entityManager->remove($foundRegistration);
        $entityManager->flush();

        //on n'affiche rien, on redirige à tous coups
        $this->addFlash('success', "Vous êtes maintenant désinscrit de cette sortie !");
        return $this->redirectToRoute('event_detail', ['id' => $id]);
    }
}
