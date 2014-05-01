<?php

namespace ProgrammingAreHard\Arbiter\Tests\Domain;

use ProgrammingAreHard\Arbiter\Domain\MaskAggregator;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Acl\Permission\PermissionMapInterface;

class MasksAggregatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MaskAggregator
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
        $this->aggregator = new MaskAggregator(
            $this->maskBuilder = $this->getMock('Symfony\Component\Security\Acl\Permission\MaskBuilder'),
            $this->permissionMap = $this->getMock('Symfony\Component\Security\Acl\Permission\PermissionMapInterface')
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
    public function valid_modes_can_be_set()
    {
        $result = $this->aggregator->setMode(MaskAggregator::MASK_ADD);
        $this->assertSame($this->aggregator, $result);

        $result = $this->aggregator->setMode(MaskAggregator::MASK_REMOVE);
        $this->assertSame($this->aggregator, $result);
    }

    /**
     * @test
     */
    public function it_adds_masks_when_mode_is_set_to_add()
    {
        $builtMask = 999;
        $this->permissionMapWillContainPermissions(true);
        $this->expectMaskBuilderReset();
        $this->expectMaskBuilderAdd(1, 0);
        $this->expectMaskBuilderAdd(2, 'VIEW');
        $this->expectMaskBuilderAdd(3, 'EDIT');
        $this->expectMaskBuilderGet($builtMask);

        $this->aggregator->setPermissions(array('VIEW', 'EDIT'));
        $result = $this->aggregator->build();

        $this->assertSame($builtMask, $result);
    }

    /**
     * @test
     */
    public function it_removes_masks_when_mode_is_set_to_remove()
    {
        $builtMask = 999;
        $this->permissionMapWillContainPermissions(true);
        $this->expectMaskBuilderReset();
        $this->expectMaskBuilderAdd(1, $initialMask = 128);
        $this->expectMaskBuilderRemove(2, 'VIEW');
        $this->expectMaskBuilderRemove(3, 'EDIT');
        $this->expectMaskBuilderGet($builtMask);

        $this->aggregator->setPermissions(array('VIEW', 'EDIT'));
        $this->aggregator->setMode(MaskAggregator::MASK_REMOVE);
        $result = $this->aggregator->build($initialMask);

        $this->assertSame($builtMask, $result);
    }

    /**
     * @test
     */
    public function get_masks_returns_a_unique_array_of_masks()
    {
        $this->permissionMapWillContainPermissions(true);
        $this->aggregator->setPermissions(array('VIEW', 'EDIT'));

        $object = new \stdClass;
        $this->expectGetMasks(0, 'VIEW', $object, array(1, 2));
        $this->expectGetMasks(1, 'EDIT', $object, array(2, 3));

        $result = $this->aggregator->getAllMasks($object);
        $this->assertSame(array(1, 2, 3), array_values($result));
    }

    /**
     * @test
     */
    public function no_permissions_returns_empty_array()
    {
        $result = $this->aggregator->getAllMasks(new \stdClass);
        $this->assertSame(array(), $result);
    }

    private function expectGetMasks($interaction = 0, $permission, $object, array $masks)
    {
        $this->permissionMap
            ->expects($this->at($interaction))
            ->method('getMasks')
            ->with($this->equalTo($permission), $this->equalTo($object))
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

    private function expectMaskBuilderRemove($interaction = 0, $permission)
    {
        $this->maskBuilder
            ->expects($this->at($interaction))
            ->method('remove')
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