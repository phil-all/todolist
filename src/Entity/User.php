<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table("user")
 * @ORM\Entity
 * @UniqueEntity("email")
 */
class User implements UserInterface
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=25, unique=true)
     * @Assert\NotBlank(message="Vous devez saisir un nom d'utilisateur.")
     */
    private string $username;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private mixed $password;

    /**
     * A non-persisted field that's used to create the encoded password.
     */
    private mixed $plainPassword;

    /**
     * @ORM\Column(type="string", length=60, unique=true)
     * @Assert\NotBlank(message="Vous devez saisir une adresse email.")
     * @Assert\Email(message="Le format de l'adresse n'est pas correcte.")
     */
    private string $email;

    /**
     * Get user id
     *
     * @return integer
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * Set username
     *
     * @param string $username
     *
     * @return self
     */
    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get user password
     *
     * @return mixed
     */
    public function getPassword(): mixed
    {
        return $this->password;
    }

    /**
     * set user password
     *
     * @param mixed $password
     *
     * @return self
     */
    public function setPassword(mixed $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get plainpassword
     *
     * @return mixed
     */
    public function getPlainPassword(): mixed
    {
        return $this->plainPassword;
    }

    /**
     * Set plainpassword
     *
     * @param mixed $plainPassword
     *
     * @return void
     */
    public function setPlainPassword(mixed $plainPassword)
    {
        $this->plainPassword = $plainPassword;
        // forces the object to look "dirty" to Doctrine. Avoids
        // Doctrine *not* saving this entity, if only plainPassword changes
        $this->password = null;
    }

    /**
     * Get user email
     *
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * set user email
     *
     * @param string $email
     *
     * @return void
     */
    public function setEmail(string $email)
    {
        $this->email = $email;
    }

    /**
     * Get user roles
     *
     * @return array
     */
    public function getRoles(): array
    {
        return array('ROLE_USER');
    }

    /**
     * Erase user credentials
     *
     * @return void
     */
    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    /**
     * Get salt
     *
     * @return mixed
     */
    public function getSalt(): mixed
    {
        return null;
    }
}
