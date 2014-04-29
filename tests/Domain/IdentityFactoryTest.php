<?php

namespace ProgrammingAreHard\Arbiter\Tests\Domain;

use ProgrammingAreHard\Arbiter\Domain\IdentityFactory;
use ProgrammingAreHard\Arbiter\Tests\stubs\Entity;
use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;

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

    public function testCanMakeObjectIdentity()
    {
        $object = new Entity(7);

        $identity = $this->factory->getObjectIdentity($object);

        $this->assertTrue($identity instanceof ObjectIdentityInterface);
        $this->assertEquals(7, $identity->getIdentifier());
        $this->assertEquals('ProgrammingAreHard\Arbiter\Tests\stubs\Entity', $identity->getType());
    }

    public function testCanMakeUserIdentity()
    {
        $user = $this->getMock('Symfony\Component\Security\Core\User\UserInterface');
        $user
            ->expects($this->any())
            ->method('getUsername')
            ->willReturn('foobar');

        $identity = $this->factory->getUserIdentity($user);
        $this->assertTrue($identity instanceof SecurityIdentityInterface);
        $this->assertSame('foobar', $identity->getUsername());
        $this->assertSame(get_class($user), $identity->getClass());

    }
} 