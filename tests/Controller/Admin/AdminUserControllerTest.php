<?php

namespace App\Tests\Controller\Admin;

use App\Entity\User;
use App\Tests\LoginTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

class AdminUserControllerTest extends WebTestCase
{
    use LoginTrait;

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

    public function testAddAccountPage()
    {
        $client = static::createClient();
        $user = self::$container->get('doctrine')->getRepository(User::class)->findOneBy(['email' => 'admin@admin.com']);
        $this->loginUser($client, $user);

        $crawler = $client->request('GET', '/admin/utilisateurs/ajouter');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Créer un compte');

        $crawler = $client->submitForm('Créer le compte', [
            'registration_form[email]' => 'bla@bla.com',
            'registration_form[lastName]' => 'bla',
            'registration_form[firstName]' => 'pouf',
            'registration_form[phone]' => '0606060606',
            'registration_form[plainPassword]' => 'blabla',
            'registration_form[role]' => 'ROLE_STUDENT',
        ]);

        $this->assertResponseRedirects('/admin/utilisateurs/ajouter', 302,'user should be redirected after registration');

        $foundUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'bla@bla.com']);
        $this->assertInstanceOf(User::class, $foundUser, 'user should be in db and retrieved by email');
    }

    public function testAddAccountExistingUser()
    {
        $client = static::createClient();
        $user = self::$container->get('doctrine')->getRepository(User::class)->findOneBy(['email' => 'admin@admin.com']);
        $this->loginUser($client, $user);

        $crawler = $client->request('GET', '/admin/utilisateurs/ajouter');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Créer un compte');

        $crawler = $client->submitForm('Créer le compte', [
            'registration_form[email]' => 'yo@yo.com',
            'registration_form[plainPassword]' => 'yoyoyo',
            'registration_form[lastName]' => 'blasdfdsfadsf',
            'registration_form[firstName]' => 'posdfdsfdsfdsfuf',
            'registration_form[phone]' => '0606090906',
            'registration_form[role]' => 'ROLE_STUDENT',
        ]);

        $this->assertResponseIsSuccessful('duplicate user insert should show the same page again');
        $this->assertSelectorTextContains('.text-red-500', 'Cet email est déjà associé à un compte !');
    }
}
