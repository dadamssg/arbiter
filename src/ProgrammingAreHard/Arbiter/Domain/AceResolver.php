<?php

namespace ProgrammingAreHard\Arbiter\Domain;

use ProgrammingAreHard\Arbiter\Model\AceResolverInterface;
use Symfony\Component\Security\Acl\Exception\NoAceFoundException;
use Symfony\Component\Security\Acl\Model\AclInterface;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;

class AceResolver implements AceResolverInterface
{
    /**
     * {@inheritdoc}
     */
    public function resolveAce(AclInterface $acl, SecurityIdentityInterface $sid)
    {
        $this->index = null;

        foreach ($acl->getObjectAces() as $index => $ace) {
            if ($ace->getSecurityIdentity()->equals($sid)) {
                return new IndexedAce($index, $ace);
            }
        }

        throw new NoAceFoundException;
    }
} 