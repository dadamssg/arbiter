<?php

namespace ProgrammingAreHard\Arbiter\Model;

use Symfony\Component\Security\Core\User\UserInterface;

interface IdentityFactoryInterface
{
    /**
     * Get object identity.
     *
     * @return \Symfony\Component\Security\Acl\Model\ObjectIdentityInterface
     */
    public function getObjectIdentity($object);

    /**
     * Get security identity.
     *
     * @return \Symfony\Component\Security\Acl\Model\SecurityIdentityInterface
     */
    public function getUserIdentity(UserInterface $user);
} 