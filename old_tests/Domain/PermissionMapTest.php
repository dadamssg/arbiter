<?php

namespace ProgrammingAreHard\Arbiter\Tests\Domain;

use ProgrammingAreHard\Arbiter\Domain\PermissionMap;
use ProgrammingAreHard\Arbiter\Model\PermissionMapInterface;
use Symfony\Component\Security\Acl\Permission\BasicPermissionMap;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;

class PermissionMapTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PermissionMapInterface
     */
    private $map;

    /**
     * Setup.
     */
    public function setUp()
    {
        $this->map = new PermissionMap;
    }

    /**
     * @test
     */
    public function it_should_only_contain_valid_permissions()
    {
        $this->assertTrue($this->map->contains('VIEW'));
        $this->assertFalse($this->map->contains('foo'));
    }

    /**
     * @test
     */
    public function it_should_convert_permissions_to_masks()
    {
        $expected = array(MaskBuilder::MASK_VIEW, MaskBuilder::MASK_EDIT, MaskBuilder::MASK_CREATE);
        $result = $this->map->permissionsToMasks(array('VIEW', 'EDIT', 'CREATE'));

        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function it_should_convert_masks_to_permissions()
    {
        $expected = array(BasicPermissionMap::PERMISSION_VIEW, BasicPermissionMap::PERMISSION_EDIT, BasicPermissionMap::PERMISSION_DELETE);
        $mask = MaskBuilder::MASK_EDIT | MaskBuilder::MASK_VIEW | MaskBuilder::MASK_DELETE;
        $result = $this->map->maskToPermissions($mask);

        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function it_returns_empty_array_if_no_permissions()
    {
        $this->assertEquals(array(), $this->map->maskToPermissions(0));
    }
} 