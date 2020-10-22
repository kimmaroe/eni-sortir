<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

class SecurityControllerTest extends WebTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
        self::ensureKernelShutdown(); //sinon il me gueule dessus
    }

    private function submitLoginForm(string $email, string $password, KernelBrowser $client): Crawler
    {
        return $client->submitForm('Connexion', [
            'login_form[email]' => $email,
            'login_form[password]' => $password,
        ]);
    }

    public function testLoginPage()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/connexion');

        $this->assertResponseIsSuccessful('login page should have a 200 code');
        $this->assertSelectorTextContains('h1', 'Connexion', 'h1 on login page should have Connexion text');

        $crawler = $this->submitLoginForm('yo@yo.com', 'yoyoyo', $client);

        $this->assertResponseRedirects('/', 302,'user should be redirected after login');
    }

    public function testLoginPageWithWrongCredentials()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/connexion');

        $crawler = $this->submitLoginForm('yo@bademail.com', 'yoyoyo', $client);
        $client->followRedirect();
        $this->assertResponseIsSuccessful('login page should be shown again on error');
        $this->assertSelectorTextContains('.text-red-500', 'Mauvais identifiants !', 'wrong email should be blocked');

        $crawler = $this->submitLoginForm('yo@yo.com', 'wrongpassword', $client);
        $client->followRedirect();
        $this->assertResponseIsSuccessful('login page should be shown again on error');
        $this->assertSelectorTextContains('.text-red-500', 'Mauvais identifiants !', 'wrong password should be blocked');

        $crawler = $this->submitLoginForm('yo@yo.com', 'YOYOYO', $client);
        $client->followRedirect();
        $this->assertResponseIsSuccessful('login page should be shown again on error');
        $this->assertSelectorTextContains('.text-red-500', 'Mauvais identifiants !', 'password in caps should be blocked');
    }

    public function testLogoutPage()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/deconnexion');

        $this->assertResponseStatusCodeSame(302, 'logout page should redirect somewhere');
        $client->followRedirect();
        $this->assertSelectorExists('a[title="Connexion"]', 'login link should be shown after logout');
    }
}
