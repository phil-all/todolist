<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Tests\ControllerTrait;
use App\Repository\UserRepository;
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
        $this->assertSelectorExists('input#user_creation_username');
        $this->assertSelectorExists('input#user_creation_password_first');
        $this->assertSelectorExists('input#user_creation_password_second');
        $this->assertSelectorExists('input#user_creation_email');
        $this->assertSelectorExists('div#user_creation_roles');
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
            'user_creation[username]'         => 'New_user',
            'user_creation[password][first]'  => 'Mot_de_passe',
            'user_creation[password][second]' => 'Mot_de_passe',
            'user_creation[email]'            => 'email@example.com',
            'user_creation[roles]'            => 'ROLE_USER'
        ]);

        $client->submit($form);

        $this->assertResponseRedirects('/users');

        $client->followRedirect();

        $this->assertSelectorTextContains('div.alert.alert-success', 'L\'utilisateur a bien été ajouté.');
    }

    /**
     * Test user role setted on creation
     */
    public function testUserRoleSettedOnCreation(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var User $user */
        $user = $entityManager->getRepository(User::class)->findOneby(['username' => 'admin_1']);

        /** @var UserRepository $userRepository */
        $userRepository = $entityManager->getRepository(User::class);

        $user = (new User())
            ->setUsername('New_user')
            ->setPassword('$2y$13$PsHRrTDnC5W5.0nZEpjen.URDZ8GF35KTRg30ang1ChTldsSh1QKu')
            ->setEmail('email@example.com');

        $userRepository->create($user);

        /** @var User $user */
        $newUser = $entityManager->getRepository(User::class)->findOneby(['username' => 'New_user']);

        $this->assertequals(true, in_array('ROLE_USER', $newUser->getRoles()));
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
            'user_creation[username]'         => '',
            'user_creation[password][first]'  => '',
            'user_creation[password][second]' => '',
            'user_creation[email]'            => ''
        ]);

        $client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
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
        $this->assertSelectorExists('input#user_edition_username');
        $this->assertSelectorTextContains('form', 'Mot de passe');
        $this->assertSelectorExists('input#user_edition_email');
        $this->assertSelectorTextContains('form', 'Tapez le mot de passe à nouveau');
        $this->assertSelectorExists('input#user_edition_password_first');
        $this->assertSelectorTextContains('form', 'Adresse email');
        $this->assertSelectorExists('input#user_edition_password_second');
        $this->assertSelectorTextContains('form', 'Roles');
        $this->assertSelectorExists('input#user_edition_roles_0');
        $this->assertSelectorExists('input#user_edition_roles_1');
        $this->assertSelectorTextContains('button.btn.btn-success', 'Modifier');
    }

    /**
     * Test simple user can't access edition page
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
            'user_edition[username]'         => 'New_user_name',
            'user_edition[password][first]'  => 'Mot_de_passe',
            'user_edition[password][second]' => 'Mot_de_passe',
            'user_edition[email]'            => 'email@example.com',
            'user_edition[roles]'            => 'ROLE_USER'
        ]);

        $client->submit($form);

        $this->assertResponseRedirects('/users');

        $client->followRedirect();

        $this->assertSelectorTextContains('div.alert.alert-success', 'L\'utilisateur a bien été modifié');
    }

    /**
     * Test user edition form submit with blank datas
     */
    public function testUserEditionFormSubmitWithBlankDatas(): void
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        /** @var User $user */
        $user = $entityManager->getRepository(User::class)->findOneby(['username' => 'admin_1']);

        $client->loginUser($user);
        $crawler = $client->request(Request::METHOD_GET, '/users/3/edit');

        $form = $crawler->selectButton('Modifier')->form([
            'user_edition[username]'         => '',
            'user_edition[password][first]'  => '',
            'user_edition[password][second]' => '',
            'user_edition[email]'            => ''
        ]);

        $client->submit($form);

        //$this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('form', 'Vous devez saisir un nom d\'utilisateur.');
        $this->assertSelectorTextContains('form', 'Vous devez saisir un mot de passe.');
        $this->assertSelectorTextContains('form', 'Vous devez saisir une adresse email.');
    }
}
