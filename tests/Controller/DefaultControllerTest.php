<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Tests\LoginTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    use LoginTrait;

    public function testHomepage()
    {
        $client = static::createClient();
        $user = self::$container->get('doctrine')->getRepository(User::class)->findOneBy(['email' => 'yo@yo.com']);
        $this->loginUser($client, $user);

        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful('homepage should work duh');
        $this->assertSelectorTextContains('.logo', 'Sortir.com', 'there should be a h1 with Sortir.com text inside');
        $this->assertEquals(18, $crawler->filter('.card-date-start')->count(), 'we should have 18 events');
    }

    public function testHomepageIsLocked()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');
        $this->assertResponseRedirects('/connexion', 302, 'homepage should redirect non logged in user to connection page');
    }
}
