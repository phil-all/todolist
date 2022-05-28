<?php

namespace App\Tests\Security;

use App\Tests\ControllerTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LoginFormAuthenticatorTest extends WebTestCase
{
    use ControllerTrait;

    /**
     * @dataProvider urlNeedAuthProvider
     */
    public function testRedirectUnauthenticatedUserToLoginPage(string $uri): void
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, $uri);

        $this->assertResponseRedirects('/login');
    }
}
