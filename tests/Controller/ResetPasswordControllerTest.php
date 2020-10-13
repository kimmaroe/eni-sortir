<?php

namespace App\Tests\Controller;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Mailer\DataCollector\MessageDataCollector;

class ResetPasswordControllerTest extends WebTestCase
{
    public function testForgotPasswordLinkIsShowingOnLoginPage()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/connexion');

        $this->assertResponseIsSuccessful();
        $lastLinkCrawler = $crawler->filter('main a')->last();
        $this->assertSame('Mot de passe oublié ?', $lastLinkCrawler->text());
    }

    public function testForgotPasswordFirstFormWithExistingEmail()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/nouveau-mot-de-passe');

        $this->assertResponseIsSuccessful();

        // enables the profiler for the next request (it does nothing if the profiler is not available)
        $client->enableProfiler();

        $crawler = $client->submitForm('Envoyer le message !', [
            'reset_password_request_form[email]' => 'yo@yo.com',
        ]);

        $this->assertResponseRedirects('/nouveau-mot-de-passe/message');
        /** @var MessageDataCollector $mailCollector */
        $profiler = $client->getProfile();
        $mailCollector = $profiler->getCollector('mailer');

        $messages = $mailCollector->getEvents()->getMessages();

        // checks that an email was sent
        $this->assertSame(1, count($messages));

        /** @var TemplatedEmail $message */
        $message = $messages[0];
        $html  = $message->getHtmlBody();

        $emailCrawler = new Crawler($html);
        $linkCrawler = $emailCrawler->filter('a');
        $this->assertSame(1, $linkCrawler->count());
    }
}
