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
        $this->assertSelectorTextContains('h1', 'Sortir.com', 'there should be a h1 with Sortir.com text inside');
        $this->assertEquals(21, $crawler->filter('tr')->count(), 'we should have 20 events + 1 tr for the table header');
    }
}
