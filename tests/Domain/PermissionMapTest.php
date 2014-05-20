<?php

namespace ProgrammingAreHard\Arbiter\Tests\Domain;

use ProgrammingAreHard\Arbiter\Domain\PermissionMap;
use Symfony\Component\Security\Acl\Permission\BasicPermissionMap;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;

class PermissionMapTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PermissionMap
     */
    private $map;

    /**
     * Setup.
     */
    public function setUp()
    {
        $this->map = new PermissionMap(New BasicPermissionMap);
    }

    /**
     * @test
     */
    public function it_supports_all_symfony_permissions()
    {
        foreach ($this->getAllPermissions() as $permission) {
            $this->assertTrue($this->map->supportsPermission($permission));
        }
    }

    /**
     * @test
     */
    public function it_returns_every_mask_for_every_permission()
    {
        foreach ($this->getAllPermissions() as $permission) {
            $this->assertGreaterThan(0, $this->map->getMask($permission));
        }
    }

    /**
     * @test
     */
    public function it_can_get_permissions_from_masks()
    {
        foreach ($this->getAllMasks() as $mask) {
            $permission = $this->map->getPermission($mask);
            $this->assertTrue($this->map->supportsPermission($permission));
        }
    }

    /**
     * @test
     */
    public function it_can_be_iterated_over()
    {
        $count = 0;

        foreach ($this->map as $permission => $mask) {
            $this->assertTrue($this->map->supportsPermission($permission));
            $permission = $this->map->getPermission($mask);
            $this->assertTrue($this->map->supportsPermission($permission));
            $count++;
        }

        $this->assertEquals(count($this->getAllPermissions()), $count);
    }

    private function getAllPermissions()
    {
        return array(
            BasicPermissionMap::PERMISSION_VIEW,
            BasicPermissionMap::PERMISSION_EDIT,
            BasicPermissionMap::PERMISSION_CREATE,
            BasicPermissionMap::PERMISSION_DELETE,
            BasicPermissionMap::PERMISSION_UNDELETE,
            BasicPermissionMap::PERMISSION_OPERATOR,
            BasicPermissionMap::PERMISSION_MASTER,
            BasicPermissionMap::PERMISSION_OWNER
        );
    }

    private function getAllMasks()
    {
        return array(
            MaskBuilder::MASK_VIEW,
            MaskBuilder::MASK_EDIT,
            MaskBuilder::MASK_CREATE,
            MaskBuilder::MASK_DELETE,
            MaskBuilder::MASK_UNDELETE,
            MaskBuilder::MASK_OPERATOR,
            MaskBuilder::MASK_MASTER,
            MaskBuilder::MASK_OWNER
        );
    }
} 