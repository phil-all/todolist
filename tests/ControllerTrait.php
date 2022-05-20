<?php

namespace App\Tests;

use Generator;
use App\Entity\User;
use Symfony\Component\DomCrawler\Form;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

trait ControllerTrait
{
    /**
     * Provide users as simple user and admin
     *
     * @return Generator
     */
    private function userProvider(): Generator
    {
        yield ['admin_1'];
        yield ['user_2'];
    }

    /**
     * Provide needed authentication uri list
     *
     * @return Generator
     */
    private function urlNeedAuthProvider(): Generator
    {
        yield ['/tasks'];
        yield ['/tasks/create'];
        yield ['/tasks/1/edit'];
        yield ['/tasks/1/toggle'];
        yield ['/tasks/1/delete'];
        yield ['/users'];
        yield ['users/create'];
        yield ['/users/1/edit'];
    }

    private function userEditionUrlProvider(): Generator
    {
        yield ['/users/1/edit'];
        yield ['/users/2/edit'];
    }

    private function isDoneProvider(): Generator
    {
        yield [false];
        yield [true];
    }

    /**
     * Get a login form to be submited
     *
     * @param string  $username
     * @param string  $password
     * @param Crawler $crawler
     *
     * @return Form
     */
    private function getLoginForm(string $username, string $password, Crawler $crawler): Form
    {
        return $crawler->selectButton('Se connecter')->form([
            '_username' => $username,
            '_password' => $password
        ]);
    }

    // /**
    //  * Generate a user session with cookie
    //  *
    //  * @param KernelBrowser $client
    //  * @param User          $user
    //  *
    //  * @return void
    //  */
    // private function generateSession(KernelBrowser $client, User $user): void
    // {
    //     /** @var Session $session */
    //     $session = $client->getContainer()->get('session');

    //     $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());

    //     $session->set('_security_main', serialize($token));
    //     $session->save();

    //     $client
    //         ->getCookieJar()
    //         ->set(new Cookie($session->getName(), $session->getId()));
    // }
}
