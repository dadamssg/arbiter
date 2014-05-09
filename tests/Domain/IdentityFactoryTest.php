<?php

namespace ProgrammingAreHard\Arbiter\Tests\Domain;

use ProgrammingAreHard\Arbiter\Domain\IdentityFactory;
use ProgrammingAreHard\Arbiter\Tests\stubs\Entity;
use ProgrammingAreHard\Arbiter\Tests\stubs\User;

class IdentityFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var IdentityFactory
     */
    private $factory;

    /**
     * Setup.
     */
    public function setUp()
    {
        $this->factory = new IdentityFactory;
    }

    /**
     * @test
     */
    public function it_should_be_able_to_create_object_identity()
    {
        $identity = $this->factory->getObjectIdentity(new Entity);

        $this->assertInstanceOf(
            'Symfony\Component\Security\Acl\Model\ObjectIdentityInterface',
            $identity
        );
    }

    /**
     * @test
     */
    public function it_should_be_able_to_create_user_identity()
    {
        $identity = $this->factory->getUserIdentity(new User);

        $this->assertInstanceOf(
            'Symfony\Component\Security\Acl\Domain\UserSecurityIdentity',
            $identity
        );
    }
} 