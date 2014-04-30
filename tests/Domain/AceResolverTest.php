<?php

namespace ProgrammingAreHard\Arbiter\Tests\Domain;

use ProgrammingAreHard\Arbiter\Domain\IndexedAceResolver;
use Symfony\Component\Security\Acl\Model\EntryInterface;
use Symfony\Component\Security\Acl\Model\MutableAclInterface;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;

class AceResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var IndexedAceResolver
     */
    private $aceResolver;

    /**
     * @var MutableAclInterface
     */
    private $acl;

    /**
     * @var SecurityIdentityInterface
     */
    private $userIdentity;

    /**
     * Setup.
     */
    public function setUp()
    {
        $this->aceResolver = new IndexedAceResolver;
        $this->acl = $this->getMock('Symfony\Component\Security\Acl\Model\MutableAclInterface');
        $this->userIdentity = $this->getMock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface');

    }

    /**
     * @test
     *
     * @expectedException \Symfony\Component\Security\Acl\Exception\NoAceFoundException
     */
    public function it_throws_exception_when_no_aces()
    {
        $this->expectAclGetObjectAcesReturns(array());

        $this->aceResolver->resolveIndexedAce($this->acl, $this->userIdentity);
    }

    /**
     * @test
     *
     * @expectedException \Symfony\Component\Security\Acl\Exception\NoAceFoundException
     */
    public function it_throws_exception_when_no_matching_aces()
    {
        $aces = $this->getAcesWithNoneMatching();
        $this->expectAclGetObjectAcesReturns($aces);

        $this->aceResolver->resolveIndexedAce($this->acl, $this->userIdentity);
    }

    /**
     * @test
     */
    public function it_sets_index_and_ace_when_ace_is_resovled()
    {
        $aces = $this->getAcesWithOneMatching();
        $this->expectAclGetObjectAcesReturns($aces);

        $indexedAce = $this->aceResolver->resolveIndexedAce($this->acl, $this->userIdentity);
        $this->assertTrue($indexedAce->getAce() instanceof EntryInterface);
        $this->assertSame(0, $indexedAce->getIndex());
    }

    private function expectAclGetObjectAcesReturns(array $aces = array())
    {
        $this->acl
            ->expects($this->any())
            ->method('getObjectAces')
            ->willReturn($aces);
    }

    private function expectUserIdentityToMatch($matches = true)
    {
        $this->userIdentity
            ->expects($this->any())
            ->method('equals')
            ->with($this->userIdentity)
            ->willReturn($matches);
    }

    private function getAcesWithOneMatching()
    {
        $this->expectUserIdentityToMatch(true);

        return array($this->newAce(), $this->newAce());
    }

    private function getAcesWithNoneMatching()
    {
        $this->expectUserIdentityToMatch(false);

        return array($this->newAce(), $this->newAce());
    }

    private function newAce()
    {
        $ace = $this->getMock('Symfony\Component\Security\Acl\Model\EntryInterface');
        $ace
            ->expects($this->any())
            ->method('getSecurityIdentity')
            ->willReturn($this->userIdentity);
        return $ace;
    }
} 