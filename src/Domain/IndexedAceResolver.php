<?php

namespace ProgrammingAreHard\Arbiter\Domain;

use ProgrammingAreHard\Arbiter\Model\IndexedAceResolverInterface;
use Symfony\Component\Security\Acl\Exception\NoAceFoundException;
use Symfony\Component\Security\Acl\Model\AclInterface;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;

class IndexedAceResolver implements IndexedAceResolverInterface
{
    /**
     * {@inheritdoc}
     */
    public function resolveIndexedAce(AclInterface $acl, SecurityIdentityInterface $sid)
    {
        foreach ($acl->getObjectAces() as $index => $ace) {
            if ($ace->getSecurityIdentity()->equals($sid)) {
                return new IndexedAce($index, $ace);
            }
        }

        throw new NoAceFoundException;
    }
} 