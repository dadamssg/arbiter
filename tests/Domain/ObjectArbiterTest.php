<?php

namespace ProgrammingAreHard\Arbiter\Tests\Domain;

use ProgrammingAreHard\Arbiter\Domain\ObjectArbiter;
use ProgrammingAreHard\Arbiter\Model\IdentityFactoryInterface;
use ProgrammingAreHard\Arbiter\Model\ObjectArbiterInterface;
use ProgrammingAreHard\Arbiter\Model\PermissionsInterface;
use ProgrammingAreHard\Arbiter\Model\PermissionsTransformerInterface;
use Symfony\Component\Security\Acl\Dbal\MutableAclProvider;
use Mockery as m;
use Symfony\Component\Security\Acl\Domain\Entry;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Exception\AclNotFoundException;
use Symfony\Component\Security\Acl\Model\AclInterface;
use Symfony\Component\Security\Acl\Model\EntryInterface;
use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ObjectArbiterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectArbiterInterface
     */
    private $arbiter;

    /**
     * @var MutableAclProvider
     */
    private $aclProvider;

    /**
     * @var PermissionsTransformerInterface
     */
    private $transformer;

    /**
     * @var IdentityFactoryInterface
     */
    private $identityFactory;

    /**
     * @var PermissionsInterface
     */
    private $permissions;

    /**
     * @var UserInterface
     */
    private $user;

    /**
     * Setup.
     */
    public function setUp()
    {
        $this->arbiter = new ObjectArbiter(
            $this->aclProvider = m::mock('Symfony\Component\Security\Acl\Model\MutableAclProviderInterface'),
            $this->transformer = m::mock('ProgrammingAreHard\Arbiter\Model\PermissionsTransformerInterface'),
            $this->identityFactory = m::mock('ProgrammingAreHard\Arbiter\Model\IdentityFactoryInterface')
        );

        $this->permissions = m::mock('ProgrammingAreHard\Arbiter\Model\PermissionsInterface');
        $this->user = m::mock('Symfony\Component\Security\Core\User\UserInterface');

    }

    /**
     * @test
     */
    public function it_creates_object_identity_when_setting_object()
    {
        $object = new \stdClass;
        $this->identityFactory->shouldReceive('getObjectIdentity')->with($object);

        $result = $this->arbiter->setObject($object);
        $this->assertSame($this->arbiter, $result);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function it_throws_exception_when_updating_permissions_with_no_object()
    {
        $this->arbiter->updatePermissions($this->user, $this->permissions);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function it_throws_exception_when_getting_permissions_with_no_object()
    {
        $this->arbiter->getPermissions($this->user);
    }

    /**
     * @test
     */
    public function it_transforms_to_permissions_when_no_acl_is_found()
    {
        $this->expectUserIdentityIsCreated($this->user);
        $objectIdentity = $this->expectObjectIdentityIsCreated($object = new \stdClass);
        $this->noAclIsFound($objectIdentity);
        $this->expectMaskIsTransformedToPermissions(0);

        $permissions = $this->arbiter
            ->setObject($object)
            ->getPermissions($this->user);

        $this->assertSame($this->permissions, $permissions);
    }

    /**
     * @test
     */
    public function it_transforms_to_permissions_when_no_ace_is_found()
    {
        $this->expectUserIdentityIsCreated($this->user);
        $objectIdentity = $this->expectObjectIdentityIsCreated($object = new \stdClass);
        $acl = $this->aclIsFound($objectIdentity);
        $this->noAcesAreFound($acl);
        $this->expectMaskIsTransformedToPermissions(0);

        $permissions = $this->arbiter
            ->setObject($object)
            ->getPermissions($this->user);

        $this->assertSame($this->permissions, $permissions);
    }

    /**
     * @test
     */
    public function it_transforms_mask_from_ace_to_permissions_acl_is_found()
    {
        $userIdentity = $this->expectUserIdentityIsCreated($this->user);
        $objectIdentity = $this->expectObjectIdentityIsCreated($object = new \stdClass);
        $acl = $this->aclIsFound($objectIdentity);
        $ace = $this->acesAreFound($acl, $userIdentity);
        $ace->shouldReceive('getMask')->andReturn($mask = 5);
        $this->expectMaskIsTransformedToPermissions($mask);

        $permissions = $this->arbiter
            ->setObject($object)
            ->getPermissions($this->user);

        $this->assertSame($this->permissions, $permissions);
    }

    /**
     * @test
     */
    public function it_updates_acl_and_ace_when_updating_permissions()
    {
        $object = new \stdClass;

        $userIdentity = $this->expectUserIdentityIsCreated($this->user);
        $objectIdentity = $this->expectObjectIdentityIsCreated($object);
        $acl = $this->aclIsFound($objectIdentity);
        $this->acesAreFound($acl, $userIdentity);
        $this->expectObjectAceWasUpdated($acl);
        $this->expectPermissionsAreTransformedToMask($mask = 5);
        $this->expectAclWasUpdated($acl);

        $this->arbiter
            ->setObject($object)
            ->updatePermissions($this->user, $this->permissions);
    }

    /**
     * @test
     */
    public function it_deletes_ace_when_updating_permissions_with_zero_mask()
    {
        $object = new \stdClass;

        $userIdentity = $this->expectUserIdentityIsCreated($this->user);
        $objectIdentity = $this->expectObjectIdentityIsCreated($object);
        $acl = $this->aclIsFound($objectIdentity);
        $this->acesAreFound($acl, $userIdentity);
        $this->expectObjectAceWasDeleted($acl);
        $this->expectPermissionsAreTransformedToMask($mask = 0);
        $this->expectAclWasUpdated($acl);

        $this->arbiter
            ->setObject($object)
            ->updatePermissions($this->user, $this->permissions);
    }

    /**
     * @test
     */
    public function it_inserts_ace_when_no_ace_is_found_when_updating_permissions()
    {
        $object = new \stdClass;

        $userIdentity = $this->expectUserIdentityIsCreated($this->user);
        $objectIdentity = $this->expectObjectIdentityIsCreated($object);
        $acl = $this->aclIsFound($objectIdentity);
        $this->noAcesAreFound($acl, $userIdentity);
        $this->expectObjectAceWasInserted($acl, $userIdentity);
        $this->expectPermissionsAreTransformedToMask($mask = 5);
        $this->expectAclWasUpdated($acl);

        $this->arbiter
            ->setObject($object)
            ->updatePermissions($this->user, $this->permissions);
    }

    /**
     * @test
     */
    public function it_creates_acl_when_no_acl_is_found_when_updating_permissions()
    {
        $object = new \stdClass;

        $userIdentity = $this->expectUserIdentityIsCreated($this->user);
        $objectIdentity = $this->expectObjectIdentityIsCreated($object);
        $this->noAclIsFound($objectIdentity);
        $acl = $this->expectAclIsCreated($objectIdentity);
        $this->acesAreFound($acl, $userIdentity);
        $this->expectObjectAceWasDeleted($acl);
        $this->expectPermissionsAreTransformedToMask($mask = 0);
        $this->expectAclWasUpdated($acl);

        $this->arbiter
            ->setObject($object)
            ->updatePermissions($this->user, $this->permissions);
    }

    private function expectAclWasUpdated(AclInterface $acl)
    {
        $this->aclProvider
            ->shouldReceive('updateAcl')
            ->with($acl);
    }

    private function expectObjectAceWasInserted(AclInterface $acl, SecurityIdentityInterface $sid)
    {
        $acl
            ->shouldReceive('insertObjectAce')
            ->with($sid, m::type('int'));
    }

    private function expectObjectAceWasUpdated(AclInterface $acl)
    {
        $acl
            ->shouldReceive('updateObjectAce')
            ->with(m::type('int'), m::type('int'));
    }

    private function expectObjectAceWasDeleted(AclInterface $acl)
    {
        $acl
            ->shouldReceive('deleteObjectAce')
            ->with(m::type('int'));
    }

    private function acesAreFound(AclInterface $acl, SecurityIdentityInterface $sid = null, $matches = true)
    {
        $ace = m::mock('Symfony\Component\Security\Acl\Model\EntryInterface');
        $sid = $sid ? : m::mock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface');
        $sid
            ->shouldReceive('equals')
            ->with($sid)
            ->andReturn($matches);

        $ace
            ->shouldReceive('getSecurityIdentity')
            ->andReturn($sid);

        $acl
            ->shouldReceive('getObjectAces')
            ->andReturn(array($ace));

        return $ace;
    }

    private function noAcesAreFound(AclInterface $acl)
    {
        $ace = m::mock('Symfony\Component\Security\Acl\Model\EntryInterface');

        $acl
            ->shouldReceive('getObjectAces')
            ->andReturn(array());

        return $ace;
    }

    private function aclIsFound(ObjectIdentityInterface $objectIdentity)
    {
        $acl = m::mock('Symfony\Component\Security\Acl\Model\MutableAclInterface');

        $this->aclProvider
            ->shouldReceive('findAcl')
            ->with($objectIdentity)
            ->andReturn($acl);

        return $acl;
    }

    private function noAclIsFound(ObjectIdentityInterface $objectIdentity)
    {
        $this->aclProvider
            ->shouldReceive('findAcl')
            ->with($objectIdentity)
            ->andThrow(new AclNotFoundException);
    }

    private function expectAclIsCreated(ObjectIdentityInterface $objectIdentity)
    {
        $acl = m::mock('Symfony\Component\Security\Acl\Model\MutableAclInterface');

        $this->aclProvider
            ->shouldReceive('createAcl')
            ->with($objectIdentity)
            ->andReturn($acl);

        return $acl;
    }

    private function expectPermissionsAreTransformedToMask($mask = 0)
    {
        $this->transformer
            ->shouldReceive('toMask')
            ->with($this->permissions)
            ->andReturn($mask);
    }

    private function expectMaskIsTransformedToPermissions($mask = 0)
    {
        $this->transformer
            ->shouldReceive('toPermissions')
            ->with($mask)
            ->andReturn($this->permissions);
    }

    private function expectUserIdentityIsCreated(UserInterface $user = null, $userIdentity = null)
    {
        $user = $user ? : m::mock('Symfony\Component\Security\Core\User\UserInterface');
        $userIdentity = $userIdentity ? : m::mock('Symfony\Component\Security\Acl\Model\SecurityIdentityInterface');

        $this->identityFactory
            ->shouldReceive('getUserIdentity')
            ->with($user)
            ->andReturn($userIdentity);

        return $userIdentity;
    }

    private function expectObjectIdentityIsCreated($object = null, $objectIdentity = null)
    {
        $object = $object ? : new \stdClass;
        $objectIdentity = $objectIdentity ? : m::mock('Symfony\Component\Security\Acl\Model\ObjectIdentityInterface');

        $this->identityFactory
            ->shouldReceive('getObjectIdentity')
            ->with($object)
            ->andReturn($objectIdentity);

        return $objectIdentity;
    }
}