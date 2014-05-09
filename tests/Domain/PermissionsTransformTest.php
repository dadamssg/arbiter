<?php

namespace ProgrammingAreHard\Arbiter\Tests\Domain;

use ProgrammingAreHard\Arbiter\Domain\PermissionsTransformer;
use Mockery as m;
use ProgrammingAreHard\Arbiter\Model\PermissionMapInterface;
use ProgrammingAreHard\Arbiter\Model\PermissionsFactoryInterface;
use Symfony\Component\Security\Acl\Permission\BasicPermissionMap;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;

class PermissionsTransformTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PermissionsTransformer
     */
    private $transformer;

    /**
     * @var PermissionMapInterface
     */
    private $map;

    /**
     * @var PermissionsFactoryInterface
     */
    private $factory;

    /**
     * @var PermissionsInterface
     */
    private $permissions;

    /**
     * Setup.
     */
    public function setUp()
    {
        $this->transformer = new PermissionsTransformer(
            $this->map = m::mock('ProgrammingAreHard\Arbiter\Model\PermissionMapInterface'),
            $this->factory = m::mock('ProgrammingAreHard\Arbiter\Model\PermissionsFactoryInterface')
        );

        $this->permissions = m::mock('ProgrammingAreHard\Arbiter\Model\PermissionsInterface');
    }

    /**
     * @test
     */
    public function it_is_instantiable()
    {
        $this->assertNotNull($this->transformer);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function it_throws_exception_when_unsupported_permission_to_mask()
    {
        $permissions = m::mock('ProgrammingAreHard\Arbiter\Model\PermissionsInterface');
        $permissions->shouldReceive('getIterator')->andReturn(new \ArrayIterator(array('foo', 'bar')));
        $this->supportsPermissions(array('foo' => false));

        $this->transformer->permissionsToMask($permissions);
    }

    /**
     * @test
     */
    public function it_can_tranform_permissions_to_mask()
    {
        $permissions = m::mock('ProgrammingAreHard\Arbiter\Model\PermissionsInterface');
        $permissions->shouldReceive('getIterator')->andReturn(new \ArrayIterator(array('foo', 'bar')));

        $this->supportsPermissions(array('foo' => true, 'bar' => true));
        $this->getMask(array('foo' => 1, 'bar' => 4));

        $mask = $this->transformer->permissionsToMask($permissions);

        $this->assertEquals(1 | 4, $mask);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function it_throws_exception_when_unsupported_permission_to_masks()
    {
        $permissions = m::mock('ProgrammingAreHard\Arbiter\Model\PermissionsInterface');
        $permissions->shouldReceive('getIterator')->andReturn(new \ArrayIterator(array('foo', 'bar')));
        $this->supportsPermissions(array('foo' => false));

        $this->transformer->permissionsToMasks($permissions);
    }

    /**
     * @test
     */
    public function it_can_tranform_permissions_to_masks()
    {
        $permissions = m::mock('ProgrammingAreHard\Arbiter\Model\PermissionsInterface');
        $permissions->shouldReceive('getIterator')->andReturn(new \ArrayIterator(array('foo', 'bar')));
        $this->supportsPermissions(array('foo' => true, 'bar' => true));
        $this->getMask(array('foo' => 1, 'bar' => 4));

        $masks = $this->transformer->permissionsToMasks($permissions);

        $this->assertEquals(array(1, 4), $masks);
    }

    /**
     * @test
     */
    public function it_can_transform_mask_to_permissions()
    {
        $viewPerm = BasicPermissionMap::PERMISSION_VIEW;
        $viewMask = MaskBuilder::MASK_VIEW;
        $editPerm = BasicPermissionMap::PERMISSION_EDIT;
        $editMask = MaskBuilder::MASK_EDIT;

        $map = array(
            $viewPerm => $viewMask,
            $editPerm => $editMask
        );

        $this->map->shouldReceive('getIterator')->andReturn(new \ArrayIterator($map));
        $this->map->shouldReceive('getPermission')->with($viewMask)->andReturn($viewPerm);
        $this->map->shouldReceive('getPermission')->with($editMask)->andReturn($editPerm);
        $this->factory->shouldReceive('newPermissions')->with(array($viewPerm, $editPerm));

        $this->transformer->maskToPermissions($viewMask | $editMask);
    }

    /**
     * @test
     */
    public function it_transforms_mask_to_empty_permissions_with_zero_mask()
    {
        $map = array(
            'foo' => 1,
            'bar' => 2
        );

        $this->map->shouldReceive('getIterator')->andReturn(new \ArrayIterator($map));
        $this->factory->shouldReceive('newPermissions')->with(array());

        $this->transformer->maskToPermissions(0);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function it_checks_supported_permissions_when_creating_new_permissions_from_an_array()
    {
        $perms = array('foo', 'bar');
        $this->supportsPermissions(array('foo' => true, 'bar' => false));

        $this->transformer->newPermissions($perms);
    }

    /**
     * @test
     */
    public function it_uses_factory_to_create_new_permissions()
    {
        $perms = array('foo', 'bar');
        $this->supportsPermissions(array('foo' => true, 'bar' => true));
        $this->expectFactoryToBeUsed($perms);

        $permissions = $this->transformer->newPermissions($perms);

        $this->assertSame($this->permissions, $permissions);
    }

    private function expectFactoryToBeUsed(array $permissions)
    {
        $this->factory
            ->shouldReceive('newPermissions')
            ->with($permissions)
            ->andReturn($this->permissions);
    }

    private function supportsPermissions(array $perms)
    {
        foreach ($perms as $permission => $isSupported) {
            $this->map->shouldReceive('supportsPermission')->with($permission)->andReturn($isSupported);
        }
    }

    private function getMask(array $masks)
    {
        foreach ($masks as $permission => $mask) {
            $this->map->shouldReceive('getMask')->with($permission)->andReturn($mask);
        }
    }
} 