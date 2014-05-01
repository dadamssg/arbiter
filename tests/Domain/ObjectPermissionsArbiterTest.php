<?php

namespace ProgrammingAreHard\Arbiter\Tests\Domain;

use ProgrammingAreHard\Arbiter\Domain\IndexedAce;
use ProgrammingAreHard\Arbiter\Domain\MaskAggregator;
use ProgrammingAreHard\Arbiter\Domain\ObjectPermissionsArbiter;
use ProgrammingAreHard\Arbiter\Model\IdentityFactoryInterface;
use ProgrammingAreHard\Arbiter\Model\IndexedAceResolverInterface;
use Symfony\Component\Security\Acl\Exception\AclNotFoundException;
use Symfony\Component\Security\Acl\Exception\NoAceFoundException;
use Symfony\Component\Security\Acl\Model\EntryInterface;
use Symfony\Component\Security\Acl\Model\MutableAclInterface;
use Symfony\Component\Security\Acl\Model\MutableAclProviderInterface;
use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\User\UserInterface;

class ObjectPermissionsArbiterTest extends \PHPUnit_Framework_TestCase
{
    const MASK = 128;

    /**
     * @var ObjectPermissionsArbiter
     */
    private $arbiter;

    /**
     * @var MutableAclProviderInterface
     */
    private $aclProvider;

    /**
     * @var IndexedAceResolverInterface
     */
    private $aceResolver;

    /**
     * @var IdentityFactoryInterface
     */
    private $identityFactory;

    /**
     * @var MaskAggregator
     */
    private $maskAggregator;

    /**
     * @var ObjectIdentityInterface
     */
    private $objectIdentity;

    /**
     * @var SecurityIdentityInterface
     */
    private $userIdentity;

    /**
     * @var MutableAclInterface
     */
    private $acl;

    /**
     * @var EntryInterface
     */
    private $ace;

    /**
     * @var object
     */
    private $object;

    /**
     * @var UserInterface
     */
    private $user;

    /**
     * Setup.
     */
    public function setUp()
    {
        $this->arbiter = new ObjectPermissionsArbiter(
            $this->aclProvider = $this->getMock('Symfony\Component\Security\Acl\Model\MutableAclProviderInterface'),
            $this->aceResolver = $this->getMock('ProgrammingAreHard\Arbiter\Model\IndexedAceResolverInterface'),
            $this->identityFactory = $this->getMock('ProgrammingAreHard\Arbiter\Model\IdentityFactoryInterface'),
            $this->maskAggregator = $this->getMock('ProgrammingAreHard\Arbiter\Model\MaskAggregatorInterface')
        );

        $this->objectIdentity = $this->getMock('Symfony\Component\Security\Acl\Model\ObjectIdentityInterface');
        $this->userIdentity = $this->getMock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface');
        $this->acl = $this->getMock('Symfony\Component\Security\Acl\Model\MutableAclInterface');
        $this->ace = $this->getMock('Symfony\Component\Security\Acl\Model\EntryInterface');
        $this->object = new \stdClass;
        $this->user = new User('foo', 'bar');
    }

    /**
     * @test
     */
    public function setting_object_creates_object_identity()
    {
        $this->expectObjectIdentityToBeCreated($object = new \stdClass);

        $result = $this->arbiter->setObject($object);
        $this->assertSame($this->arbiter, $result);
    }

    /**
     * @test
     */
    public function permissions_get_set_on_mask_aggregator()
    {
        $permissions = array('VIEW', 'EDIT');
        $this->expectMaskAggregatorsPermissionsToBeSet($permissions);

        $result = $this->arbiter->setPermissions($permissions);
        $this->assertSame($this->arbiter, $result);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function granting_with_no_object_throws_exception()
    {
        $this->arbiter->grant($this->user);
    }

    /**
     * @test
     */
    public function granting_with_no_acl_creates_new_acl()
    {
        $this->expectObjectIdentityToBeCreated($this->object);
        $this->aclWillNotBeFound();
        $this->expectAclToBeCreated();
        $this->expectUserIdentityToBeCreated($this->user);
        $this->aceWillNotBeFound();
        $this->expectMaskToBeBuiltWithoutAceAndReturn();
        $this->expectAceToBeCreated();
        $this->expectAclToBeUpdated();

        $this->arbiter
            ->setObject($this->object)
            ->grant($this->user);
    }

    /**
     * @test
     */
    public function granting_with_existing_acl_and_ace_get_updated()
    {
        $this->expectObjectIdentityToBeCreated($this->object);
        $this->aclWillBeFound();
        $this->expectUserIdentityToBeCreated($this->user);
        $this->aceWillBeFound($indexedAce = new IndexedAce(1, $this->ace));
        $this->expectMaskToBeBuiltWithAceAndReturn();
        $this->expectAceToBeUpdated($indexedAce);
        $this->expectAclToBeUpdated();

        $this->arbiter
            ->setObject($this->object)
            ->grant($this->user);
    }

    /**
     * @test
     */
    public function granting_with_no_mask_deletes_ace()
    {
        $this->expectObjectIdentityToBeCreated($this->object);
        $this->aclWillBeFound();
        $this->expectUserIdentityToBeCreated($this->user);
        $this->aceWillBeFound($indexedAce = new IndexedAce(1, $this->ace));
        $this->expectMaskToBeBuiltWithAceAndReturn(MaskAggregator::MASK_ADD, 0);
        $this->expectAceToBeDeleted($indexedAce);
        $this->expectAclToBeUpdated();

        $this->arbiter
            ->setObject($this->object)
            ->grant($this->user);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function revoking_with_no_object_throws_exception()
    {
        $this->arbiter->revoke($this->user);
    }

    /**
     * @test
     */
    public function revoking_with_no_acl_does_nothing()
    {
        $this->expectObjectIdentityToBeCreated($this->object);
        $this->aclWillNotBeFound();
        $this->expectAclNotToBeUpdated();
        $this->arbiter
            ->setObject($this->object)
            ->revoke($this->user);
    }

    /**
     * @test
     */
    public function revoking_with_no_ace_does_nothing()
    {
        $this->expectObjectIdentityToBeCreated($this->object);
        $this->expectUserIdentityToBeCreated($this->user);
        $this->aclWillBeFound();
        $this->aceWillNotBeFound();
        $this->expectAclNotToBeUpdated();

        $this->arbiter
            ->setObject($this->object)
            ->revoke($this->user);
    }

    /**
     * @test
     */
    public function revoking_with_ace_updates_acl()
    {
        $this->expectObjectIdentityToBeCreated($this->object);
        $this->expectUserIdentityToBeCreated($this->user);
        $this->aclWillBeFound();
        $this->aceWillBeFound($indexedAce = new IndexedAce(1, $this->ace));
        $this->expectMaskToBeBuiltWithAceAndReturn(MaskAggregator::MASK_REMOVE);
        $this->expectAclToBeUpdated();

        $this->arbiter
            ->setObject($this->object)
            ->revoke($this->user);
    }

    /**
     * @test
     */
    public function revoking_with_no_mask_deletes_ace()
    {
        $this->expectObjectIdentityToBeCreated($this->object);
        $this->aclWillBeFound();
        $this->expectUserIdentityToBeCreated($this->user);
        $this->aceWillBeFound($indexedAce = new IndexedAce(1, $this->ace));
        $this->expectMaskToBeBuiltWithAceAndReturn(MaskAggregator::MASK_REMOVE, 0);
        $this->expectAceToBeDeleted($indexedAce);
        $this->expectAclToBeUpdated();

        $this->arbiter
            ->setObject($this->object)
            ->revoke($this->user);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function is_granted_with_no_object_throws_exception()
    {
        $this->arbiter->isGranted($this->user);
    }

    /**
     * @test
     */
    public function is_granted_with_no_permissions_returns_true()
    {
        $this->expectObjectIdentityToBeCreated($this->object);
        $this->getAllMasksFromAggregatorWillReturn(array());

        $granted = $this->arbiter
            ->setObject($this->object)
            ->isGranted($this->user);

        $this->assertTrue($granted);
    }

    /**
     * @test
     */
    public function is_granted_with_no_acl_returns_false()
    {
        $this->expectObjectIdentityToBeCreated($this->object);
        $this->getAllMasksFromAggregatorWillReturn(array('VIEW', 'EDIT'));
        $this->aclWillNotBeFound();

        $granted = $this->arbiter
            ->setObject($this->object)
            ->isGranted($this->user);

        $this->assertFalse($granted);
    }

    /**
     * @test
     */
    public function is_granted_with_acl_returns_true()
    {
        $permissions = array('VIEW', 'EDIT');

        $this->expectObjectIdentityToBeCreated($this->object);
        $this->getAllMasksFromAggregatorWillReturn($permissions);
        $this->aclWillBeFound();
        $this->expectUserIdentityToBeCreated($this->user);
        $this->expectAclGrantCheck($permissions, true);

        $granted = $this->arbiter
            ->setObject($this->object)
            ->isGranted($this->user);

        $this->assertTrue($granted);
    }

    private function expectAclGrantCheck($permissions, $granted = true)
    {
        $this->acl
            ->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo($permissions), array($this->userIdentity))
            ->willReturn($granted);
    }

    private function getAllMasksFromAggregatorWillReturn($permissions)
    {
        $this->maskAggregator
            ->expects($this->once())
            ->method('getAllMasks')
            ->with($this->object)
            ->willReturn($permissions);
    }

    private function aceWillBeFound(IndexedAce $indexedAce)
    {
        $this->aceResolver
            ->expects($this->once())
            ->method('resolveIndexedAce')
            ->with($this->equalTo($this->acl), $this->equalTo($this->userIdentity))
            ->willReturn($indexedAce);
    }

    private function expectMaskToBeBuiltWithAceAndReturn($mode = MaskAggregator::MASK_ADD, $returnMask = self::MASK)
    {
        $this->expectMaskAggreatorModeSet($mode);

        $this->ace
            ->expects($this->once())
            ->method('getMask')
            ->willReturn(self::MASK);

        $this->maskAggregator
            ->expects($this->once())
            ->method('build')
            ->with(self::MASK)
            ->willReturn($returnMask);
    }

    private function expectMaskToBeBuiltWithoutAceAndReturn($returnMask = self::MASK)
    {
        $this->expectMaskAggreatorModeSet(MaskAggregator::MASK_ADD);

        $this->maskAggregator
            ->expects($this->once())
            ->method('build')
            ->willReturn($returnMask);
    }

    private function expectMaskAggreatorModeSet($mode)
    {
        $this->maskAggregator
            ->expects($this->once())
            ->method('setMode')
            ->with($mode)
            ->willReturn($this->maskAggregator);
    }

    private function expectAceToBeUpdated(IndexedAce $indexedAce)
    {
        $this->acl
            ->expects($this->once())
            ->method('updateObjectAce')
            ->with($this->equalTo($indexedAce->getIndex()), $this->equalTo(self::MASK));
    }

    private function expectAceToBeDeleted(IndexedAce $indexedAce)
    {
        $this->acl
            ->expects($this->once())
            ->method('deleteObjectAce')
            ->with($this->equalTo($indexedAce->getIndex()));
    }

    private function expectAclNotToBeUpdated()
    {
        $this->aclProvider
            ->expects($this->never())
            ->method('updateObjectAce')
            ->with($this->anything(), $this->anything());

    }

    private function expectAclToBeUpdated()
    {
        $this->aclProvider
            ->expects($this->once())
            ->method('updateAcl')
            ->with($this->equalTo($this->acl));
    }

    private function expectAceToBeCreated()
    {
        $this->acl
            ->expects($this->once())
            ->method('insertObjectAce')
            ->with($this->equalTo($this->userIdentity), $this->equalTo(self::MASK));
    }

    private function aclWillBeFound()
    {
        $this->aclProvider
            ->expects($this->once())
            ->method('findAcl')
            ->with($this->objectIdentity)
            ->willReturn($this->acl);
    }

    private function expectAclToBeCreated()
    {
        $this->aclProvider
            ->expects($this->once())
            ->method('createAcl')
            ->with($this->objectIdentity)
            ->willReturn($this->acl);
    }

    public function expectAclNotToBeCreated()
    {
        $this->aclProvider
            ->expects($this->never())
            ->method('createAcl')
            ->with($this->anything())
            ->willReturn($this->acl);
    }

    private function aclWillNotBeFound()
    {
        $this->aclProvider
            ->expects($this->once())
            ->method('findAcl')
            ->with($this->objectIdentity)
            ->will($this->throwException(new AclNotFoundException));
    }

    private function aceWillNotBeFound()
    {
        $this->aceResolver
            ->expects($this->once())
            ->method('resolveIndexedAce')
            ->with($this->equalTo($this->acl), $this->equalTo($this->userIdentity))
            ->will($this->throwException(new NoAceFoundException));
    }

    private function expectMaskAggregatorsPermissionsToBeSet($permissions)
    {
        $this->maskAggregator
            ->expects($this->once())
            ->method('setPermissions')
            ->with($this->equalTo($permissions));
    }

    private function expectObjectIdentityToBeCreated($object)
    {
        $this->identityFactory
            ->expects($this->once())
            ->method('getObjectIdentity')
            ->with($this->equalTo($object))
            ->willReturn($this->objectIdentity);
    }

    private function expectUserIdentityToBeCreated(UserInterface $user)
    {
        $this->identityFactory
            ->expects($this->once())
            ->method('getUserIdentity')
            ->with($this->equalTo($user))
            ->willReturn($this->userIdentity);
    }
} 