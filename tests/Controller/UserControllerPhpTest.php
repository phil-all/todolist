<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Tests\ControllerTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerPhpTest extends WebTestCase
{
    use ControllerTrait;

    /**
     * Test admin access users list
     */
    public function testAdminAccessUsersList(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var User $user */
        $user = $entityManager->getRepository(User::class)->findOneby(['username' => 'admin_1']);

        $userCount = count($entityManager->getRepository(User::class)->findAll());

        $client->loginUser($user);
        $crawler = $client->request(Request::METHOD_GET, '/users');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertEquals($userCount, $crawler->filter('a.btn.btn-success')->count());
        $this->assertSelectorTextContains('table > thead', 'Nom d\'utilisateur');
        $this->assertSelectorTextContains('table > thead', 'Adresse d\'utilisateur');
        $this->assertSelectorTextContains('table > thead', 'Actions');
    }

    /**
     * Test simple user can't acces users list
     */
    public function testSimpeUserCanNotAccessUsersList(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var User $user */
        $user = $entityManager->getRepository(User::class)->findOneby(['username' => 'user_2']);

        $client->loginUser($user);
        $client->request(Request::METHOD_GET, '/users');

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Test admin can access user creation page
     */
    public function testAdminCanAccessUserCreationPage(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var User $user */
        $user = $entityManager->getRepository(User::class)->findOneby(['username' => 'admin_1']);

        $client->loginUser($user);
        $client->request(Request::METHOD_GET, '/users/create');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * Test simple user can not acces user creation page
     */
    public function testSimpleUserCanNotAccesUserCreationPage(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var User $user */
        $user = $entityManager->getRepository(User::class)->findOneby(['username' => 'user_2']);

        $client->loginUser($user);
        $client->request(Request::METHOD_GET, '/users/create');

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Test user creation form fields and button
     */
    public function testUserCreationForm(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var User $user */
        $user = $entityManager->getRepository(User::class)->findOneby(['username' => 'admin_1']);

        $client->loginUser($user);
        $client->request(Request::METHOD_GET, '/users/create');

        $this->assertSelectorExists('form');
        $this->assertSelectorExists('input#user_username');
        $this->assertSelectorExists('input#user_password_first');
        $this->assertSelectorExists('input#user_password_second');
        $this->assertSelectorExists('input#user_email');
        $this->assertSelectorExists('input#user__token');
        $this->assertSelectorTextContains('button.btn.btn-success', 'Ajouter');
    }

    /**
     * Test user creation form submit with valid datas
     */
    public function testUserCreationFormWithValidDatas(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var User $user */
        $user = $entityManager->getRepository(User::class)->findOneby(['username' => 'admin_1']);

        $client->loginUser($user);
        $crawler = $client->request(Request::METHOD_GET, '/users/create');

        $form = $crawler->selectButton('Ajouter')->form([
            'user[username]'         => 'New_user',
            'user[password][first]'  => 'Mot_de_passe',
            'user[password][second]' => 'Mot_de_passe',
            'user[email]'            => 'email@example.com'
        ]);

        $client->submit($form);

        $this->assertResponseRedirects('/users');

        $client->followRedirect();

        $this->assertSelectorTextContains('div.alert.alert-success', 'L\'utilisateur a bien été ajouté.');
    }

    /**
     * Test user creation form submit with blank datas
     */
    public function testUserCreationFormSubmitWithBlankDatas(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var User $user */
        $user = $entityManager->getRepository(User::class)->findOneby(['username' => 'admin_1']);

        $client->loginUser($user);
        $crawler = $client->request(Request::METHOD_GET, '/users/create');

        $form = $crawler->selectButton('Ajouter')->form([
            'user[username]'         => '',
            'user[password][first]'  => '',
            'user[password][second]' => '',
            'user[email]'            => ''
        ]);

        $client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorExists('form');
        $this->assertSelectorExists('input#user_username');
        $this->assertSelectorExists('input#user_password_first');
        $this->assertSelectorExists('input#user_password_second');
        $this->assertSelectorExists('input#user_email');
        $this->assertSelectorExists('input#user__token');
        $this->assertSelectorTextContains('button.btn.btn-success', 'Ajouter');
        $this->assertSelectorTextContains('form', 'Vous devez saisir un nom d\'utilisateur.');
        $this->assertSelectorTextContains('form', 'Vous devez saisir un mot de passe.');
        $this->assertSelectorTextContains('form', 'Vous devez saisir une adresse email.');
    }

    /**
     * Test admin access all users edtion page
     * @dataProvider userEditionUrlProvider
     */
    public function testAdminAccessAllUsersEditionPage(string $url): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var User $user */
        $user = $entityManager->getRepository(User::class)->findOneby(['username' => 'admin_1']);

        $client->loginUser($user);
        $client->request(Request::METHOD_GET, $url);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('form', 'Nom d\'utilisateur');
        $this->assertSelectorTextContains('form', 'Mot de passe');
        $this->assertSelectorTextContains('form', 'Tapez le mot de passe à nouveau');
        $this->assertSelectorTextContains('form', 'Adresse email');
        $this->assertSelectorExists('input#user__token');
        $this->assertSelectorTextContains('button.btn.btn-success', 'Modifier');
    }

    /**
     * Test simple user can acces own edition page
     */
    public function testSimpleUserCanAccesOwnEditionPage(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var User $user */
        $user = $entityManager->getRepository(User::class)->findOneby(['username' => 'user_2']);

        $client->loginUser($user);
        $client->request(Request::METHOD_GET, '/users/2/edit');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('form', 'Nom d\'utilisateur');
        $this->assertSelectorTextContains('form', 'Mot de passe');
        $this->assertSelectorTextContains('form', 'Tapez le mot de passe à nouveau');
        $this->assertSelectorTextContains('form', 'Adresse email');
        $this->assertSelectorExists('input#user__token');
        $this->assertSelectorTextContains('button.btn.btn-success', 'Modifier');
    }

    /**
     * Test simple user can't access other user edition page
     */
    public function testSimpleUserCanNotAccessUserEditionPage(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var User $user */
        $user = $entityManager->getRepository(User::class)->findOneby(['username' => 'user_2']);

        $client->loginUser($user);
        $client->request(Request::METHOD_GET, '/users/3/edit');

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Test user edition form with valid datas
     */
    public function testUserEditionFormWithValidDatas(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var User $user */
        $user = $entityManager->getRepository(User::class)->findOneby(['username' => 'admin_1']);

        $client->loginUser($user);
        $crawler = $client->request(Request::METHOD_GET, '/users/3/edit');

        $form = $crawler->selectButton('Modifier')->form([
            'user[username]'         => 'New_user',
            'user[password][first]'  => 'Mot_de_passe',
            'user[password][second]' => 'Mot_de_passe',
            'user[email]'            => 'email@example.com'
        ]);

        $client->submit($form);

        $this->assertResponseRedirects('/users');

        $client->followRedirect();

        $this->assertSelectorTextContains('div.alert.alert-success', 'L\'utilisateur a bien été modifié');
    }
}
