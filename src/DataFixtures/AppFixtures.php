<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Event;
use App\Entity\EventState;
use App\Entity\Registration;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $passwordEncoder;
    private $manager;
    private $faker;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->faker = \Faker\Factory::create('fr_FR');
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->loadStates();
        $this->loadUsers();
        $this->loadCampus();
        $this->loadEvents();
        $this->loadRegistrations();
    }

    private function loadCampus()
    {
        $names = ["Nantes", "Rennes", "Niort"];
        foreach($names as $name){
            $campus = new Campus();
            $campus->setName($name);
            $this->manager->persist($campus);
        }

        $this->manager->flush();
    }

    private function loadRegistrations()
    {
        $allUsers = $this->manager->getRepository(User::class)->findAll();
        $allEvents = $this->manager->getRepository(Event::class)->findAll();
        foreach($allUsers as $user){
            $events = $this->faker->randomElements($allEvents, $this->faker->numberBetween(0, 3));
            foreach($events as $event){
                $reg = new Registration();
                $reg->setUser($user);
                $reg->setEvent($event);
                $reg->setDateRegistered($this->faker->dateTimeBetween($event->getDateCreated(), $event->getDateEnd()));
                $this->manager->persist($reg);
            }
        }

        $this->manager->flush();
    }

    private function loadEvents()
    {
        $allUsers = $this->manager->getRepository(User::class)->findAll();
        $allCampuses = $this->manager->getRepository(Campus::class)->findAll();
        $now = new \DateTime();
        for($i = 0; $i < 100; $i++){
            $event = new Event();
            $event->setTitle(substr($this->faker->sentence, 0, 30));
            $event->setDescription($this->faker->text(1000));

            $event->setDateCreated($this->faker->dateTimeBetween("-1 month"));

            $event->setDateUpdated($this->faker->optional()->dateTimeBetween($event->getDateCreated()));

            $event->setDateStart($this->faker->dateTimeBetween($event->getDateCreated(), "+1 month"));

            $dateRegistrationEnded = (clone $event->getDateStart())->sub(new \DateInterval('P'.mt_rand(1,4).'D'));
            $event->setDateRegistrationEnded($dateRegistrationEnded);

            $endDate = clone $event->getDateStart();
            $endDate->add(new \DateInterval('P'.mt_rand(0,3).'DT'.mt_rand(1,6).'H'));
            $event->setDateEnd($endDate);

            $event->setMaxRegistrations($this->faker->numberBetween(4, 100));
            $event->setCreator($this->faker->randomElement($allUsers));

            if ($event->getDateEnd() < $now){
                $states = $this->manager->getRepository(EventState::class)->findStatesBetween(['Passée', 'Annulée']);
            }
            elseif ($event->getDateStart() < $now && $event->getDateEnd() > $now) {
                $states = $this->manager->getRepository(EventState::class)->findStatesBetween(['Activité en cours']);
            }
            elseif ($event->getDateStart() > $now && $event->getDateRegistrationEnded() < $now) {
                $states = $this->manager->getRepository(EventState::class)->findStatesBetween(['Clôturée']);
            }
            else {
                $states = $this->manager->getRepository(EventState::class)->findStatesBetween(['Créée', 'Ouverte']);
            }

            $event->setState($this->faker->randomElement($states));

            $event->setCampus($this->faker->randomElement($allCampuses));

            $this->manager->persist($event);
        }
        $this->manager->flush();
    }

    private function loadStates()
    {
        $stateNames = ["Créée", "Ouverte", "Clôturée", "Activité en cours", "Passée", "Annulée"];
        foreach($stateNames as $name) {
            $state = new EventState($name);
            $this->manager->persist($state);
        }
        $this->manager->flush();
    }

    private function loadUsers()
    {
        $user = new User();
        $user->setEmail('yo@yo.com');
        //yoyoyo
        $user->setPassword('$argon2id$v=19$m=65536,t=4,p=1$MThOQzNHdi9yOW9paFdtRg$pkpCD/3fRLjHn8AQ1gHBlB9IFHhO2tp4R4x2htUk8gw');
        $user->setRoles(['ROLE_STUDENT']);
        $user->setIsActive(true);
        $user->setFirstName('yo');
        $user->setLastName('yo');
        $user->setPhone('0606060606');
        $user->setDateCreated(new \DateTime());

        $this->manager->persist($user);

        $user = new User();
        $user->setEmail('admin@admin.com');
        //adminadmin
        $user->setPassword('$argon2id$v=19$m=65536,t=4,p=1$VS8zN0d3NXdKUEpNQ0drcA$/SMDmHEWsgzJ0SCsLZxMIV9QwifduDiTO9TGhhTVh34');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setIsActive(true);
        $user->setFirstName('admin');
        $user->setLastName('admin');
        $user->setPhone('0606060607');
        $user->setDateCreated(new \DateTime("-1 month"));

        $this->manager->persist($user);

        for($i = 0; $i < 30; $i++){
            $user = new User();
            $user->setEmail($this->faker->unique()->email());
            $user->setPassword( $this->passwordEncoder->encodePassword($user, $user->getEmail()) );
            $user->setRoles(['ROLE_STUDENT']);
            $user->setIsActive(true);
            $user->setFirstName($this->faker->firstName);
            $user->setLastName($this->faker->lastName);
            $user->setPhone($this->faker->unique()->phoneNumber);
            $user->setDateCreated($this->faker->dateTimeBetween('-1 year'));
            $this->manager->persist($user);
        }

        $this->manager->flush();
    }
}
