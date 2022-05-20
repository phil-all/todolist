<?php

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    private user $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = new User();
    }

    public function testUserGettersAndSetters(): void
    {
        $username  = 'username';
        $password  = 'password';
        $email     = 'email@example.test';
        $roles     = ['ROLE_USER'];

        $this->user->setUsername($username)
            ->setPassword($password)
            ->setEmail($email)
            ->setRoles($roles);

        $this->assertEquals($username, $this->user->getUsername());
        $this->assertEquals($username, $this->user->getUserIdentifier());
        $this->assertEquals($password, $this->user->getPassword());
        $this->assertEquals($email, $this->user->getEmail());
        $this->assertEquals($roles, $this->user->getRoles());
        $this->assertNull($this->user->getId());
    }

    public function testUserPasswordAndPlainPasswordOnChange(): void
    {
        $plainPassword = 'plainPassword';

        $this->user->setPlainPassword('plainPassword');

        $this->assertNull($this->user->getPassword());
        $this->assertEquals($plainPassword, $this->user->getPlainPassword());
    }

    public function testEraseCredentials(): void
    {
        $this->user->eraseCredentials();

        $this->assertNull($this->user->getPlainPassword());
    }
}
