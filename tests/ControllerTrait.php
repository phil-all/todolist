<?php

namespace App\Tests;

use Generator;
use App\Entity\User;
//use Symfony\Component\DomCrawler\Form;
use Doctrine\ORM\EntityRepository;
//use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

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
     * Provide tasks list url
     *
     * @return Genertator
     */
    private function tasksListUrlProvider(): Generator // @phpstan-ignore-line
    {
        yield ['/tasks/todo'];
        yield ['/tasks/done'];
    }

    /**
     * Provide admin pages url
     *
     * @return Generator
     */
    public function adminPagesUrl(): Generator
    {
        yield ['/users'];
        yield ['/users/create'];
        yield ['/users/3/edit'];
        yield ['/users/3/delete'];
    }

    /**
     * Provide needed authentication uri list
     *
     * @return Generator
     */
    private function urlNeedAuthProvider(): Generator
    {
        yield ['/tasks/todo'];
        yield ['/tasks/done'];
        yield ['/tasks/create'];
        yield ['/tasks/1/edit'];
        yield ['/tasks/1/toggle'];
        yield ['/tasks/1/delete'];
        yield ['/users'];
        yield ['users/create'];
        yield ['/users/1/edit'];
    }

    /**
     * Provide task state
     *
     * @return Generator
     */
    private function isDoneProvider(): Generator
    {
        yield [false];
        yield [true];
    }

    /**
     * Provide a user role
     *
     * @return Generator
     */
    private function roleProvider(): Generator
    {
        yield ['ROLE_ADMIN'];
        yield ['ROLE_USER'];
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

    /**
     * Get a repository
     *
     * @param KernelBrowser $client
     * @param string        $className
     * @psalm-param class-string<T> $className
     *
     * @return EntityRepository
     * @template T of object
     */
    public function getRepository(KernelBrowser $client, string $className): EntityRepository
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        return $entityManager->getRepository($className);
    }

    /**
     * Get User from username
     *
     * @param KernelBrowser $client
     * @param string        $userName
     *
     * @return User
     */
    private function getUser(KernelBrowser $client, string $userName): User
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');

        return $entityManager->getRepository(User::class)->findOneby(['username' => $userName]);
    }
}
