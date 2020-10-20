<?php

namespace App\Command;

use App\Entity\Event;
use App\Entity\EventState;
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
        $io = new SymfonyStyle($input, $output);

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

        $io->success('Finito. ' . count($eventsToArchive) . " sortie(s) archivée(s).");
        return 0;
    }
}
