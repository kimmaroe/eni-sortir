<?php

namespace App\Tests;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Permet de rendre disponible la méthode permettant de simuler la connexion du user
 * Une méthode existe en Symfony 5.1 pour ça !
 */
trait LoginTrait
{
    //piqué à grafikart ici : https://www.grafikart.fr/tutoriels/tests-symfony-controller-1217
    public function loginUser(KernelBrowser $client, User $user)
    {
        $session = $client->getContainer()->get('session');
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $session->set('_security_main', serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($cookie);
    }
}