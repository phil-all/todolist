<?php

namespace App\Tests\Controller;

use App\Tests\ControllerTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    use ControllerTrait;

    /**
     * @dataProvider userProvider
     */
    public function testHomePageTitleAndButtonsVisiblesByAythenticatedUser(string $username): void
    {
        $client  = static::createClient();
        $crawler = $client->request('GET', '/login');

        $client->submit($this->getLoginForm(
            $username,
            'pass1234',
            $crawler
        ));

        $this->assertResponseRedirects('/');

        $client->followRedirect();

        $this->assertSelectorTextContains('h1', 'Bienvenue sur Todo List');
        $this->assertSelectorTextContains('a.btn.btn-danger', 'Se déconnecter');
        $this->assertSelectorTextContains('a.btn.btn-success', 'Créer une nouvelle tâche');
        $this->assertSelectorTextContains('a.btn.btn-info', 'Consulter la liste des tâches à faire');
        $this->assertSelectorTextContains('a.btn.btn-warning', 'Consulter la liste des tâches terminées');
    }

    public function testAdminButtonVisiblesByAdmin(): void
    {
        $client  = static::createClient();
        $crawler = $client->request('GET', '/login');

        $client->submit($this->getLoginForm(
            'admin_1',
            'pass1234',
            $crawler
        ));

        $this->assertResponseRedirects('/');

        $client->followRedirect();

        $this->assertSelectorTextContains('a.btn.btn-primary', 'Gestion des utilisateurs');
    }

    public function testAdminButtonNotVisibleByNonAdminUser(): void
    {
        $client  = static::createClient();
        $crawler = $client->request('GET', '/login');

        $client->submit($this->getLoginForm(
            'user_2',
            'pass1234',
            $crawler
        ));

        $this->assertResponseRedirects('/');

        $client->followRedirect();

        $this->assertSelectorTextNotContains('a.btn', 'Gestion des utilisateur');
    }
}
