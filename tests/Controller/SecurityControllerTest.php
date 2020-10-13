<?php

namespace App\Controller\Tests;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

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

    public function testRegisterPage()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/register');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Créer un compte');

        $crawler = $client->submitForm('Créer le compte', [
            'registration_form[email]' => 'bla@bla.com',
            'registration_form[lastName]' => 'bla',
            'registration_form[firstName]' => 'pouf',
            'registration_form[phone]' => '0606060606',
            'registration_form[plainPassword]' => 'blabla',
        ]);

        $this->assertResponseRedirects('/', 302,'user should be redirected after registration');

        $foundUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'bla@bla.com']);
        $this->assertInstanceOf(User::class, $foundUser, 'user should be in db and retrieved by email');
    }

    public function testRegisterPageExistingUser()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/register');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Créer un compte');

        $crawler = $client->submitForm('Créer le compte', [
            'registration_form[email]' => 'yo@yo.com',
            'registration_form[plainPassword]' => 'yoyoyo',
        ]);

        $this->assertResponseIsSuccessful('duplicate user insert should show the same page again');
        $this->assertSelectorTextContains('form ul li', 'Cet email est déjà associé à un compte !');
    }

    public function testLoginPage()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/login');

        $this->assertResponseIsSuccessful('login page should have a 200 code');
        $this->assertSelectorTextContains('h1', 'Connexion', 'h1 on login page should have Connexion text');

        $crawler = $client->submitForm('Connexion', [
            'email' => 'yo@yo.com',
            'password' => 'yoyoyo',
        ]);

        $this->assertResponseRedirects('/', 302,'user should be redirected after login');
    }

    public function testLoginPageWithWrongCredentials()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/register');

        //first register the user
        $crawler = $client->submitForm('Créer le compte', [
            'registration_form[email]' => 'yo@yo.com',
            'registration_form[plainPassword]' => 'yoyoyo',
        ]);

        $crawler = $client->request('GET', '/login');

        $crawler = $client->submitForm('Connexion', [
            'email' => 'yo@yo.com222',
            'password' => 'yoyoyo',
        ]);
        $client->followRedirect();
        $this->assertResponseIsSuccessful('login page should be shown again on error');
        $this->assertSelectorTextContains('.alert', 'Email could not be found.', 'wrong email should be blocked');

        $crawler = $client->submitForm('Connexion', [
            'email' => 'yo@yo.com',
            'password' => 'yoyoyofjadsklfjdsa',
        ]);

        $client->followRedirect();
        $this->assertResponseIsSuccessful('login page should be shown again on error');
        $this->assertSelectorTextContains('.alert', 'Invalid credentials.', 'wrong password should be blocked');

        $crawler = $client->submitForm('Connexion', [
            'email' => 'yo@yo.com',
            'password' => 'YOYOYO',
        ]);
        $client->followRedirect();
        $this->assertResponseIsSuccessful('login page should be shown again on error');
        $this->assertSelectorTextContains('.alert', 'Invalid credentials.', 'password in caps should be blocked');
    }

    public function testLogoutPage()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/logout');

        $this->assertResponseStatusCodeSame(302, 'logout page should redirect somewhere');
        $client->followRedirect();
        $this->assertSelectorExists('a[title="Connexion"]', 'login link should be shown after logout');
    }
}
