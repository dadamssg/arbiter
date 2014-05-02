<?php

namespace ProgrammingAreHard\Arbiter\Tests\Domain;

use ProgrammingAreHard\Arbiter\Domain\PermissionsMaskAggregator;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use ProgrammingAreHard\Arbiter\Model\PermissionMapInterface;

class PermissionsMasksAggregatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PermissionsMaskAggregator
     */
    private $aggregator;

    /**
     * @var MaskBuilder
     */
    private $maskBuilder;

    /**
     * @var PermissionMapInterface
     */
    private $permissionMap;

    /**
     * Setup.
     */
    public function setUp()
    {
        $this->aggregator = new PermissionsMaskAggregator(
            $this->maskBuilder = $this->getMock('Symfony\Component\Security\Acl\Permission\MaskBuilder'),
            $this->permissionMap = $this->getMock('ProgrammingAreHard\Arbiter\Model\PermissionMapInterface')
        );
    }

    /**
     * @test
     */
    public function single_permission_can_be_set()
    {
        $this->permissionMapWillContainPermissions(true);

        $result = $this->aggregator->setPermissions('VIEW');
        $this->assertSame($this->aggregator, $result);
    }

    /**
     * @test
     */
    public function multiple_permissions_can_be_set()
    {
        $this->permissionMapWillContainPermissions(true);

        $result = $this->aggregator->setPermissions(array('VIEW', 'EDIT'));
        $this->assertSame($this->aggregator, $result);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function it_throws_exception_when_invalid_permission()
    {
        $this->permissionMapWillContainPermissions(false);

        $this->aggregator->setPermissions(array('foo', 'bar'));
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function it_throws_exception_when_invalid_mode()
    {
        $this->aggregator->setMode('foo');
    }

    /**
     * @test
     */
    public function its_mode_can_be_set()
    {
        $result = $this->aggregator->setMode(PermissionsMaskAggregator::MASK_ADD);
        $this->assertSame($this->aggregator, $result);

        $result = $this->aggregator->setMode(PermissionsMaskAggregator::MASK_REMOVE);
        $this->assertSame($this->aggregator, $result);
    }

    /**
     * @test
     */
    public function it_combines_permission_when_set_to_add()
    {
        $grantingPermission = 'EDIT';

        $this->allowAnyPermission();
        $this->expectMaskBuilderReset();
        $this->expectMaskToPermissions($initialMask = 5, $beginPermissions = array('VIEW'));
        $this->expectPermissionsToMasks(
            array_merge($beginPermissions, array($grantingPermission)),
            $returnMasks = array(1, 4)
        );
        $this->expectMaskBuilderAdd(1, 1);
        $this->expectMaskBuilderAdd(2, 4);
        $this->expectMaskBuilderGet($finalMask = 8);

        $result = $this->aggregator
            ->setPermissions($grantingPermission)
            ->build($initialMask);

        $this->assertEquals($finalMask, $result);
    }

    /**
     * @test
     */
    public function it_combines_permissions_when_set_to_add()
    {
        $grantingPermission = array('EDIT', 'DELETE');

        $this->allowAnyPermission();
        $this->expectMaskBuilderReset();
        $this->expectMaskToPermissions($initialMask = 5, $beginPermissions = array('VIEW'));
        $this->expectPermissionsToMasks(
            array_merge($beginPermissions, $grantingPermission),
            $returnMasks = array(1, 4, 8)
        );
        $this->expectMaskBuilderAdd(1, 1);
        $this->expectMaskBuilderAdd(2, 4);
        $this->expectMaskBuilderAdd(3, 8);

        $this->expectMaskBuilderGet($returnMast = 8);

        $result = $this->aggregator
            ->setPermissions($grantingPermission)
            ->build($initialMask);

        $this->assertEquals($returnMast, $result);
    }

    /**
     * @test
     */
    public function it_removes_permission_when_set_to_remove()
    {
        $revokingPermission = 'EDIT';

        $this->allowAnyPermission();
        $this->expectMaskBuilderReset();
        $this->expectMaskToPermissions($initialMask = 5, $beginPermissions = array('VIEW', 'EDIT'));
        $this->expectPermissionsToMasks(
            array_diff($beginPermissions, array($revokingPermission)),
            $returnMasks = array(1)
        );
        $this->expectMaskBuilderAdd(1, 1);
        $this->expectMaskBuilderGet($finalMask = 8);

        $result = $this->aggregator
            ->setMode(PermissionsMaskAggregator::MASK_REMOVE)
            ->setPermissions($revokingPermission)
            ->build($initialMask);

        $this->assertEquals($finalMask, $result);
    }

    /**
     * @test
     */
    public function it_removes_permissions_when_set_to_remove()
    {
        $revokingPermission = array('VIEW', 'EDIT');

        $this->allowAnyPermission();
        $this->expectMaskBuilderReset();
        $this->expectMaskToPermissions($initialMask = 5, $beginPermissions = array('VIEW', 'EDIT'));
        $this->expectPermissionsToMasks(
            array_diff($beginPermissions, $revokingPermission),
            $returnMasks = array()
        );
        $this->expectMaskBuilderGet($finalMask = 0);

        $result = $this->aggregator
            ->setMode(PermissionsMaskAggregator::MASK_REMOVE)
            ->setPermissions($revokingPermission)
            ->build($initialMask);

        $this->assertEquals($finalMask, $result);
    }

    /**
     * @test
     */
    public function it_gets_masks_from_permission_map()
    {
        $this->allowAnyPermission();
        $permissions = array('VIEW', 'EDIT');
        $this->expectPermissionsToMasks($permissions, $masksFromPermissionsMap = array(1, 4));

        $masks = $this->aggregator
            ->setPermissions($permissions)
            ->getMasks(new \stdClass);

        $this->assertEquals($masksFromPermissionsMap, $masks);
    }

    private function allowAnyPermission()
    {
        $this->permissionMap
            ->expects($this->any())
            ->method('contains')
            ->with($this->anything())
            ->willReturn(true);
    }

    private function expectMaskToPermissions($mask = 0, array $permissions = array())
    {
        $this->permissionMap
            ->expects($this->once())
            ->method('maskToPermissions')
            ->with($this->equalTo($mask))
            ->willReturn($permissions);
    }

    private function expectPermissionsToMasks(array $permissions = array(), array $masks = array())
    {
        $this->permissionMap
            ->expects($this->once())
            ->method('permissionsToMasks')
            ->with($this->equalTo($permissions))
            ->willReturn($masks);
    }

    private function permissionMapWillContainPermissions($contains = true)
    {
        $this->permissionMap
            ->expects($this->any())
            ->method('contains')
            ->with($this->anything())
            ->willReturn($contains);
    }

    private function expectMaskBuilderReset()
    {
        $this->maskBuilder
            ->expects($this->once())
            ->method('reset');
    }

    private function expectMaskBuilderAdd($interaction = 0, $permission)
    {
        $this->maskBuilder
            ->expects($this->at($interaction))
            ->method('add')
            ->with($this->equalTo($permission));
    }

    private function expectMaskBuilderGet($mask)
    {
        $this->maskBuilder
            ->expects($this->once())
            ->method('get')
            ->willReturn($mask);
    }
} 