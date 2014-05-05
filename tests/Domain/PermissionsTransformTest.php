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
     * Setup.
     */
    public function setUp()
    {
        $this->transformer = new PermissionsTransformer(
            $this->map = m::mock('ProgrammingAreHard\Arbiter\Model\PermissionMapInterface'),
            $this->factory = m::mock('ProgrammingAreHard\Arbiter\Model\PermissionsFactoryInterface')
        );
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
        $this->map->shouldReceive('supportsPermission')->with('foo')->andReturn(false);

        $this->transformer->permissionsToMask($permissions);
    }

    /**
     * @test
     */
    public function it_can_tranform_permissions_to_mask()
    {
        $permissions = m::mock('ProgrammingAreHard\Arbiter\Model\PermissionsInterface');
        $permissions->shouldReceive('getIterator')->andReturn(new \ArrayIterator(array('foo', 'bar')));
        $this->map->shouldReceive('supportsPermission')->with('foo')->andReturn(true);
        $this->map->shouldReceive('supportsPermission')->with('bar')->andReturn(true);
        $this->map->shouldReceive('getMask')->with('foo')->andReturn(1);
        $this->map->shouldReceive('getMask')->with('bar')->andReturn(4);

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
        $this->map->shouldReceive('supportsPermission')->with('foo')->andReturn(false);

        $this->transformer->permissionsToMasks($permissions);
    }

    /**
     * @test
     */
    public function it_can_tranform_permissions_to_masks()
    {
        $permissions = m::mock('ProgrammingAreHard\Arbiter\Model\PermissionsInterface');
        $permissions->shouldReceive('getIterator')->andReturn(new \ArrayIterator(array('foo', 'bar')));
        $this->map->shouldReceive('supportsPermission')->with('foo')->andReturn(true);
        $this->map->shouldReceive('supportsPermission')->with('bar')->andReturn(true);
        $this->map->shouldReceive('getMask')->with('foo')->andReturn(1);
        $this->map->shouldReceive('getMask')->with('bar')->andReturn(4);

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
} 