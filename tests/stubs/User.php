<?php

namespace ProgrammingAreHard\Arbiter\Tests\stubs;

use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface
{
    public function getId()
    {
        return 2;
    }

    public function getRoles()
    {
    }

    public function getPassword()
    {
    }

    public function getSalt()
    {
    }

    public function getUsername()
    {
        return 'foo bar';
    }

    public function eraseCredentials()
    {
    }
} 