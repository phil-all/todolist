<?php

namespace App\Tests\Controller;

use App\Tests\ControllerTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecuritycontrollerTest extends WebTestCase
{
    use ControllerTrait;

    /**
     * @dataProvider userProvider
     */
    public function testAdminSubmitLoginForm(string $username): void
    {
        $client  = static::createClient();
        $crawler = $client->request('GET', '/login');

        $client->submit($this->getLoginForm(
            $username,
            'pass1234',
            $crawler
        ));

        $this->assertResponseRedirects('/');
    }

    public function testSubmitLoginFormWithBadCredentials(): void
    {
        $client  = static::createClient();
        $crawler = $client->request('GET', '/login');

        $client->submit($this->getLoginForm(
            'invalid_user',
            'pass1234',
            $crawler
        ));

        $this->assertResponseRedirects('/login');
    }
}
