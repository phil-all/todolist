<?php

namespace App\Tests\Controller;

use App\Tests\ControllerTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecuritycontrollerTest extends WebTestCase
{
    use ControllerTrait;

    /**
     * Test login with valid creadentials
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

    /**
     * Test login with invalid credentials
     */
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
