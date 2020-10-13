<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $passwordEncoder;
    private $manager;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->faker = \Faker\Factory::create('fr_FR');
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;

        $this->loadUsers();
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
