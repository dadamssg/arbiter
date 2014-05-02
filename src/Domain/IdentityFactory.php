<?php

namespace ProgrammingAreHard\Arbiter\Domain;

use ProgrammingAreHard\Arbiter\Model\IdentityFactoryInterface;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Core\User\UserInterface;

class IdentityFactory implements IdentityFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getObjectIdentity($object)
    {
        return ObjectIdentity::fromDomainObject($object);
    }

    /**
     * {@inheritdoc}
     */
    public function getUserIdentity(UserInterface $user)
    {
        return UserSecurityIdentity::fromAccount($user);
    }
} 