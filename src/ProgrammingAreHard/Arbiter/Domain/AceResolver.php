<?php

namespace ProgrammingAreHard\Arbiter\Domain;

use ProgrammingAreHard\Arbiter\Model\AceResolverInterface;
use Symfony\Component\Security\Acl\Exception\NoAceFoundException;
use Symfony\Component\Security\Acl\Model\AclInterface;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;

class AceResolver implements AceResolverInterface
{
    /**
     * ACE index.
     *
     * @var int
     */
    private $index;

    /**
     * {@inheritdoc}
     */
    public function resolveAce(AclInterface $acl, SecurityIdentityInterface $sid)
    {
        $this->index = null;

        foreach ($acl->getObjectAces() as $index => $ace) {
            if ($ace->getSecurityIdentity()->equals($sid)) {
                $this->index = $index;
                return $ace;
            }
        }

        throw new NoAceFoundException;
    }

    /**
     * {@inheritdoc}
     */
    public function getAceIndex()
    {
        return $this->index;
    }

} 