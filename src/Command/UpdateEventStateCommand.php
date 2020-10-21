<?php

namespace App\Command;

use App\Entity\Event;
use App\Entity\EventState;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Cette commande est destinée à être appelée par un cron job (tâche planifiée) à chaque minute, en permanence
 * Elle place les éventuelles sorties en état "archivée" lorsqu'elles sont terminées ou annulées depuis un mois
 * Elle place également les sorties en "clôturée" lorsque la date de clôture des inscriptions est dépassée
 *
 */

class UpdateEventStateCommand extends Command
{
    protected static $defaultName = 'app:update-event-state';
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * UpdateEventStateCommand constructor.
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }


    protected function configure()
    {
        $this
            ->setDescription("Passe l'état des sorties en archivée ou clôturée en fonction de la date")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        //archive les vieilles sorties
        $this->archiveEvents();
        //ferme les événements ayant atteint leur nombre de places max ou si la date de clôture est dépassée
        $this->closeEvents();
        //si on augmente le nombre de place ou si ya des désinscriptions
        $this->reopenEvents();

        $this->io->success('Finito.');
        return 0;
    }


    protected function reopenEvents()
    {
        /** @var EventRepository $eventRepository */
        $eventRepository = $this->entityManager->getRepository(Event::class);

        //les événements dont l'inscription doit être fermée maintenant
        $eventsToReopen = $eventRepository->findEventsToReopen();

        //récupère l'état "closed"
        $openState = $this->entityManager->getRepository(EventState::class)->findOneBy(['name' => EventState::OPEN]);

        //change l'état de tous ces événements
        foreach($eventsToReopen as $event){
            $event->setState($openState);
            $event->setDateUpdated(new \DateTime());
            $this->entityManager->persist($event);
        }

        $this->entityManager->flush();

        $this->io->writeln(count($eventsToReopen) . " sortie(s) ont été réouverte(s).");
    }

    protected function closeEvents()
    {
        /** @var EventRepository $eventRepository */
        $eventRepository = $this->entityManager->getRepository(Event::class);

        //les événements dont l'inscription doit être fermée maintenant
        $eventsToClose = $eventRepository->findEventsToClose();

        //récupère l'état "closed"
        $closedState = $this->entityManager->getRepository(EventState::class)->findOneBy(['name' => EventState::CLOSED]);

        //change l'état de tous ces événements
        foreach($eventsToClose as $event){
            $event->setState($closedState);
            $event->setDateUpdated(new \DateTime());
            $this->entityManager->persist($event);
        }

        $this->entityManager->flush();

        $this->io->writeln(count($eventsToClose) . " sortie(s) fermée(s).");
    }

    protected function archiveEvents()
    {
        /** @var EventRepository $eventRepository */
        $eventRepository = $this->entityManager->getRepository(Event::class);

        //trouve les sorties actuellement terminée ou annulée depuis un mois pour les archiver
        $eventsToArchive = $eventRepository->findOldEventsToArchive();

        //récupère l'état "archivé"
        $archivedState = $this->entityManager->getRepository(EventState::class)->findOneBy(['name' => EventState::ARCHIVED]);

        //change l'état de tous ces événements
        foreach($eventsToArchive as $event){
            $event->setState($archivedState);
            $event->setDateUpdated(new \DateTime());
            $this->entityManager->persist($event);
        }

        $this->entityManager->flush();

        $this->io->writeln(count($eventsToArchive) . " sortie(s) archivée(s).");
    }
}
