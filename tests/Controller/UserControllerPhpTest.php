<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Tests\ControllerTrait;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerPhpTest extends WebTestCase
{
    use ControllerTrait;

    /**
     * @dataProvider adminPagesUrl
     */
    public function testNonAdminUserCanNotAccessAdministration(string $url): void
    {
        $client = static::createClient();

        /** @var User $user */
        $user = $this->getUser($client, 'user_2');

        $client->loginUser($user);
        $client->request(Request::METHOD_GET, '/users');

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testAdminAccessUsersList(): void
    {
        $client = static::createClient();

        /** @var User $user */
        $user = $this->getUser($client, 'admin_1');

        $userCount = count($this->getRepository($client, User::class)->findAll());

        $client->loginUser($user);
        $crawler = $client->request(Request::METHOD_GET, '/users');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertEquals($userCount, $crawler->filter('a.btn.btn-success')->count());
        $this->assertSelectorTextContains('table > thead', 'Nom d\'utilisateur');
        $this->assertSelectorTextContains('table > thead', 'Adresse d\'utilisateur');
        $this->assertSelectorTextContains('table > thead', 'Actions');
    }

    public function testAdminCanAccessUserCreationPage(): void
    {
        $client = static::createClient();

        /** @var User $user */
        $user = $this->getUser($client, 'admin_1');

        $client->loginUser($user);
        $client->request(Request::METHOD_GET, '/users/create');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }


    public function testUserCreationForm(): void
    {
        $client = static::createClient();

        /** @var User $user */
        $user = $this->getUser($client, 'admin_1');

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

    public function testUserCreationFormWithValidDatas(): void
    {
        $client = static::createClient();

        /** @var User $user */
        $user = $this->getUser($client, 'admin_1');

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

    public function testUserRoleSettedOnCreation(): void
    {
        $client = static::createClient();

        /** @var User $user */
        $user = $this->getUser($client, 'admin_1');

        /** @var UserRepository $userRepository */
        $userRepository = $this->getRepository($client, User::class);

        $user = (new User())
            ->setUsername('New_user')
            ->setPassword('$2y$13$PsHRrTDnC5W5.0nZEpjen.URDZ8GF35KTRg30ang1ChTldsSh1QKu')
            ->setEmail('email@example.com');

        $userRepository->create($user);

        /** @var User $newUser */
        $newUser = $this->getUser($client, 'New_user');

        $this->assertequals(true, in_array('ROLE_USER', $newUser->getRoles()));
    }

    public function testUserCreationFormSubmitWithBlankDatas(): void
    {
        $client = static::createClient();

        /** @var User $user */
        $user = $this->getUser($client, 'admin_1');

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

    public function testAdminAccessAllUsersEditionPage(): void
    {
        $client = static::createClient();

        /** @var User $user */
        $user = $this->getUser($client, 'admin_1');

        $client->loginUser($user);
        $client->request(Request::METHOD_GET, '/users/3/edit');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('form', 'Nom d\'utilisateur');
        $this->assertSelectorExists('input#user_edition_username');
        $this->assertSelectorExists('input#user_edition_email');
        $this->assertSelectorTextContains('form', 'Adresse email');
        $this->assertSelectorTextContains('form', 'Roles');
        $this->assertSelectorExists('input#user_edition_roles_0');
        $this->assertSelectorExists('input#user_edition_roles_1');
        $this->assertSelectorTextContains('button.btn.btn-success', 'Modifier');
    }

    /**
     * @dataProvider roleProvider
     */
    public function testUserEditionFormWithValidDatas(string $role): void
    {
        $client = static::createClient();

        /** @var User $user */
        $user = $this->getUser($client, 'admin_1');

        $client->loginUser($user);
        $crawler = $client->request(Request::METHOD_GET, '/users/3/edit');

        $form = $crawler->selectButton('Modifier')->form([
            'user_edition[username]'         => 'New_user_name',
            'user_edition[email]'            => 'email@example.com',
            'user_edition[roles]'            => $role
        ]);

        $client->submit($form);

        $this->assertResponseRedirects('/users');

        $client->followRedirect();

        $this->assertSelectorTextContains('div.alert.alert-success', 'L\'utilisateur a bien été modifié');
    }

    public function testUserEditionFormSubmitWithBlankDatas(): void
    {
        $client = static::createClient();

        /** @var User $user */
        $user = $this->getUser($client, 'admin_1');

        $client->loginUser($user);
        $crawler = $client->request(Request::METHOD_GET, '/users/3/edit');

        $form = $crawler->selectButton('Modifier')->form([
            'user_edition[username]'         => '',
            'user_edition[email]'            => ''
        ]);

        $client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('form', 'Vous devez saisir un nom d\'utilisateur.');
        $this->assertSelectorTextContains('form', 'Vous devez saisir une adresse email.');
    }
}
