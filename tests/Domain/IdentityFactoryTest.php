<?php

namespace ProgrammingAreHard\Arbiter\Tests\Domain;

use ProgrammingAreHard\Arbiter\Domain\IdentityFactory;
use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;
use Symfony\Component\Security\Core\User\User;

class IdentityFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var IdentityFactory
     */
    private $factory;

    public function setUp()
    {
        $this->factory = new IdentityFactory;
    }

    /**
     * @test
     */
    public function it_can_make_object_identity()
    {
        $object = $this->newObject(7);

        $identity = $this->factory->getObjectIdentity($object);

        $this->assertTrue($identity instanceof ObjectIdentityInterface);
        $this->assertEquals(7, $identity->getIdentifier());
        $this->assertEquals(get_class($object), $identity->getType());
    }

    /**
     * @test
     */
    public function it_can_make_user_identity()
    {
        $user = new User('foo', 'bar');

        $identity = $this->factory->getUserIdentity($user);

        $this->assertTrue($identity instanceof SecurityIdentityInterface);
        $this->assertSame('foo', $identity->getUsername());
        $this->assertSame(get_class($user), $identity->getClass());
    }

    private function newObject($id)
    {
        $object = $this->getMock('Symfony\Component\Security\Acl\Model\EntryInterface');
        $object
            ->expects($this->once())
            ->method('getId')
            ->willReturn($id);
        return $object;
    }
}