<?php

namespace App\Controller\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testLogouPage()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful('homepage should work duh');
        $this->assertSelectorTextContains('h1', 'Sortir.com', 'there should be a h1 with Sortir.com text inside');
    }
}
