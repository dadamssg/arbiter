<?php

namespace ProgrammingAreHard\Arbiter\Model;

use Symfony\Component\Security\Acl\Model\AclInterface;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;

interface AceResolverInterface
{
    /**
     * Find the ACE from the ACL and security identity.
     *
     * @param AclInterface $acl
     * @param SecurityIdentityInterface $sid
     * @return \Symfony\Component\Security\Acl\Model\EntryInterface
     * @throws \Symfony\Component\Security\Acl\Exception\NoAceFoundException
     */
    public function resolveAce(AclInterface $acl, SecurityIdentityInterface $sid);

    /**
     * Get the found ACE's index.
     *
     * @return int
     */
    public function getAceIndex();
} 