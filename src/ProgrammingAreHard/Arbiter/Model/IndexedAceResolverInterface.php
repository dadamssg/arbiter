<?php

namespace ProgrammingAreHard\Arbiter\Model;

use Symfony\Component\Security\Acl\Model\AclInterface;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;

interface IndexedAceResolverInterface
{
    /**
     * Find the ACE from the ACL and security identity.
     *
     * @param AclInterface $acl
     * @param SecurityIdentityInterface $sid
     * @return \ProgrammingAreHard\Arbiter\Domain\IndexedAce
     * @throws \Symfony\Component\Security\Acl\Exception\NoAceFoundException
     */
    public function resolveAce(AclInterface $acl, SecurityIdentityInterface $sid);
} 