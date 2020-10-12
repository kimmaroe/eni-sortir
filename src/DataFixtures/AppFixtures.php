<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setEmail('yo@yo.com');
        $user->setPassword('$argon2id$v=19$m=65536,t=4,p=1$MThOQzNHdi9yOW9paFdtRg$pkpCD/3fRLjHn8AQ1gHBlB9IFHhO2tp4R4x2htUk8gw');
        $user->setRoles(['ROLE_STUDENT']);
        $user->setIsActive(true);
        $user->setFirstName('yo');
        $user->setLastName('yo');
        $user->setPhone('0606060606');
        $user->setDateCreated(new \DateTime());

        $manager->persist($user);
        $manager->flush();
    }
}
