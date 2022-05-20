<?php

namespace App\Tests\Security;

use App\Tests\ControllerTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LoginFormAuthenticatorTest extends WebTestCase
{
    use ControllerTrait;

    /**
     * Test redirect unauthenticated user to login page for url needed authentication.
     * See authenticator start() method.
     * @dataProvider urlNeedAuthProvider
     */
    public function testRedirectUnauthenticatedUserToLoginPage(string $uri): void
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, $uri);

        $this->assertResponseRedirects('/login');
    }
}
