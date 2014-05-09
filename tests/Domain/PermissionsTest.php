<?php

namespace ProgrammingAreHard\Arbiter\Tests\Domain;

use ProgrammingAreHard\Arbiter\Domain\Permissions;

class PermissionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Permissions
     */
    private $permissions;

    /**
     * Setup.
     */
    public function setUp()
    {
        $this->permissions = new Permissions;
    }

    /**
     * @test
     */
    public function it_is_instantiable()
    {
        $this->assertNotNull($this->permissions);
    }

    /**
     * @test
     */
    public function it_starts_with_empty_permissions()
    {
        $this->assertEquals(array(), $this->permissions->toArray());
    }

    /**
     * @test
     */
    public function it_can_be_initiated_with_permissions()
    {
        $perms = array('foo', 'bar', 'baz');
        $permissions = new Permissions($perms);

        $this->assertEquals($perms, $permissions->toArray());
    }

    /**
     * @test
     */
    public function it_should_accept_additions()
    {
        $this->permissions->add('foo');
        $this->permissions->add('bar');

        $this->assertEquals(array('foo', 'bar'), $this->permissions->toArray());
    }

    /**
     * @test
     */
    public function it_should_accept_removals()
    {
        $permissions = new Permissions(array('foo', 'bar'));
        $permissions->remove('bar');

        $this->assertEquals(array('foo'), $permissions->toArray());
    }

    /**
     * @test
     */
    public function it_can_check_if_permission_exists()
    {
        $permissions = new Permissions(array('foo', 'bar'));
        $this->assertTrue($permissions->contains('foo'));
        $this->assertFalse($permissions->contains('baz'));
    }

    /**
     * @test
     */
    public function it_should_be_iterable()
    {
        $perms = array('foo', 'bar', 'baz');
        $permissions = new Permissions($perms);

        foreach ($permissions as $permission) {
            $this->assertTrue(in_array($permission, $perms));
        }
    }

    /**
     * @test
     */
    public function it_should_be_countable()
    {
        $permissions = new Permissions;
        $this->assertEquals(0, count($permissions));

        $permissions = new Permissions(array('foo', 'bar', 'baz'));
        $this->assertEquals(3, count($permissions));
    }

    /**
     * @test
     */
    public function it_can_be_cast_to_a_string()
    {
        $perms = array('foo', 'bar');
        $permissions = new Permissions($perms);

        $this->assertEquals('foo,bar', (string) $permissions);
    }
} 