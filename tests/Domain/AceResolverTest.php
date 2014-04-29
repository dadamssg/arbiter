<?php

namespace ProgrammingAreHard\Arbiter\Tests\Domain;

use ProgrammingAreHard\Arbiter\Domain\IndexedAceResolver;
use ProgrammingAreHard\Arbiter\Domain\IndexedAce;
use Symfony\Component\Security\Acl\Model\EntryInterface;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;

class AceResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var IndexedAceResolver
     */
    private $aceResolver;

    public function setUp()
    {
        $this->aceResolver = new IndexedAceResolver;
    }

    /**
     * @expectedException \Symfony\Component\Security\Acl\Exception\NoAceFoundException
     */
    public function testNoAcesThrowsException()
    {
        $acl = $this->newAcl();

        $acl
            ->expects($this->any())
            ->method('getObjectAces')
            ->willReturn(array());

        $sid = $this->newSid();

        $this->aceResolver->resolveAce($acl, $sid);
    }

    /**
     * @expectedException \Symfony\Component\Security\Acl\Exception\NoAceFoundException
     */
    public function testNonMatchingAcesThrowsException()
    {
        $acl = $this->newAcl();

        $acl
            ->expects($this->any())
            ->method('getObjectAces')
            ->willReturn($this->getAcesWithNoneMatching());

        $sid = $this->newSid();

        $this->aceResolver->resolveAce($acl, $sid);
    }

    public function testIndexGetsSetWhenAceIsResolved()
    {
        $acl = $this->newAcl();

        $sid = $this->newSid();

        $aces = $this->getAcesWithOneMatching($sid);

        $acl
            ->expects($this->any())
            ->method('getObjectAces')
            ->willReturn($aces);

        $indexedAce = $this->aceResolver->resolveAce($acl, $sid);
        $this->assertTrue($indexedAce->getAce() instanceof EntryInterface);
        $this->assertSame(0, $indexedAce->getIndex());
    }

    private function getAcesWithOneMatching(SecurityIdentityInterface $sid)
    {
        $sid
            ->expects($this->once())
            ->method('equals')
            ->with($sid)
            ->willReturn(true);

        $ace = $this->newAce();
        $ace
            ->expects($this->once())
            ->method('getSecurityIdentity')
            ->willReturn($sid);

        return array(
            $ace,
            $this->newAce()
        );
    }

    private function getAcesWithNoneMatching()
    {
        $sid = $this->newSid();
        $sid
            ->expects($this->any())
            ->method('equals')
            ->with($sid)
            ->willReturn(false);

        $ace1 = $this->newAce();
        $ace1
            ->expects($this->once())
            ->method('getSecurityIdentity')
            ->willReturn($sid);

        $ace2 = $this->newAce();
        $ace2
            ->expects($this->once())
            ->method('getSecurityIdentity')
            ->willReturn($sid);

        return array(
            $ace1,
            $ace2
        );
    }

    private function newAce()
    {
        return $this->getMock('Symfony\Component\Security\Acl\Model\EntryInterface');
    }

    private function newAcl()
    {
        return $this->getMock('Symfony\Component\Security\Acl\Model\AclInterface');
    }

    private function newSid()
    {
        return $this->getMock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface');
    }
} 