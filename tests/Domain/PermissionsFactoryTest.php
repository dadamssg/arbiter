<?php

namespace ProgrammingAreHard\Arbiter\Tests\Domain;

use ProgrammingAreHard\Arbiter\Domain\PermissionsFactory;

class PermissionsFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PermissionsFactory
     */
    private $factory;

    /**
     * Setup.
     */
    public function setUp()
    {
        $this->factory = new PermissionsFactory;
    }

    /**
     * @test
     */
    public function it_can_create_empty_permissions()
    {
        $this->assertEquals(array(), $this->factory->newPermissions()->toArray());
    }

    /**
     * @test
     */
    public function it_can_create_with_permissions()
    {
        $permissions = $this->factory->newPermissions(array('foo', 'bar'));

        $this->assertEquals(array('foo', 'bar'), $permissions->toArray());
    }
} 