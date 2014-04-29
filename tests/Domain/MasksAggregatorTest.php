<?php

namespace ProgrammingAreHard\Arbiter\Tests\Domain;

use ProgrammingAreHard\Arbiter\Domain\MasksAggregator;

class MasksAggregatorTest extends \PHPUnit_Framework_TestCase
{
    private $aggregator;

    private $maskBuilder;

    private $permissionMap;

    public function setUp()
    {
        $this->aggregator = new MasksAggregator(
            $this->maskBuilder = $this->getMock('Symfony\Component\Security\Acl\Permission\MaskBuilder'),
            $this->permissionMap = $this->getMock('Symfony\Component\Security\Acl\Permission\PermissionMapInterface')
        );
    }

    public function testValidPermission()
    {
        $this->permissionMap
            ->expects($this->once())
            ->method('contains')
            ->with($this->anything())
            ->willReturn(true);

        $result = $this->aggregator->setPermissions('VIEW');
        $this->assertSame($this->aggregator, $result);
    }

    public function testValidPermissions()
    {
        $this->permissionMap
            ->expects($this->any())
            ->method('contains')
            ->with($this->anything())
            ->will($this->onConsecutiveCalls(true, true));

        $result = $this->aggregator->setPermissions(array('VIEW', 'EDIT'));
        $this->assertSame($this->aggregator, $result);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidPermissionThrowsException()
    {
        $this->permissionMap
            ->expects($this->any())
            ->method('contains')
            ->with($this->anything())
            ->will($this->onConsecutiveCalls(true, false));

        $this->aggregator->setPermissions(array('foo', 'bar'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidModeThrowsException()
    {
        $this->aggregator->setMode('foo');
    }

    public function testValidModes()
    {
        $result = $this->aggregator->setMode(MasksAggregator::MASK_ADD);
        $this->assertSame($this->aggregator, $result);

        $result = $this->aggregator->setMode(MasksAggregator::MASK_REMOVE);
        $this->assertSame($this->aggregator, $result);
    }

    public function testBuildAddsMasksByDefault()
    {
        $this->permissionMap
            ->expects($this->any())
            ->method('contains')
            ->with($this->anything())
            ->will($this->onConsecutiveCalls(true, true));

        $this->aggregator->setPermissions(array('VIEW', 'EDIT'));

        $this->maskBuilder
            ->expects($this->once())
            ->method('reset');

        $this->maskBuilder
            ->expects($this->at(1))
            ->method('add')
            ->with($this->equalTo(0));

        $this->maskBuilder
            ->expects($this->at(2))
            ->method('add')
            ->with($this->equalTo('VIEW'));

        $this->maskBuilder
            ->expects($this->at(3))
            ->method('add')
            ->with($this->equalTo('EDIT'));

        $this->maskBuilder
            ->expects($this->once())
            ->method('get')
            ->willReturn(999);

        $result = $this->aggregator->build();

        $this->assertSame(999, $result);
    }

    public function testBuildRemovesMasksWhenModeIsSet()
    {
        $this->permissionMap
            ->expects($this->any())
            ->method('contains')
            ->with($this->anything())
            ->will($this->onConsecutiveCalls(true, true));

        $this->aggregator->setPermissions(array('VIEW', 'EDIT'));
        $this->aggregator->setMode(MasksAggregator::MASK_REMOVE);

        $initialMask = 28;

        $this->maskBuilder
            ->expects($this->once())
            ->method('reset');

        $this->maskBuilder
            ->expects($this->once())
            ->method('add')
            ->with($this->equalTo($initialMask));

        $this->maskBuilder
            ->expects($this->at(2))
            ->method('remove')
            ->with($this->equalTo('VIEW'));

        $this->maskBuilder
            ->expects($this->at(3))
            ->method('remove')
            ->with($this->equalTo('EDIT'));

        $this->maskBuilder
            ->expects($this->once())
            ->method('get')
            ->willReturn(999);

        $result = $this->aggregator->build($initialMask);

        $this->assertSame(999, $result);
    }

    public function testGetMasksReturnsUniqueArrayOfMasks()
    {
        $this->permissionMap
            ->expects($this->any())
            ->method('contains')
            ->with($this->anything())
            ->will($this->onConsecutiveCalls(true, true));

        $this->aggregator->setPermissions(array('VIEW', 'EDIT'));

        $object = new \stdClass;
        $firstSet = array(1, 2);
        $secondSet = array(2, 3);

        $this->permissionMap
            ->expects($this->at(0))
            ->method('getMasks')
            ->with($this->equalTo('VIEW'), $this->equalTo($object))
            ->willReturn($firstSet);

        $this->permissionMap
            ->expects($this->at(1))
            ->method('getMasks')
            ->with($this->equalTo('EDIT'), $this->equalTo($object))
            ->willReturn($secondSet);

        $result = $this->aggregator->getAllMasks($object);
        $this->assertSame(array(1, 2, 3), array_values($result));
    }

    public function testNoPermissionsReturnsEmptyArray()
    {
        $result = $this->aggregator->getAllMasks(new \stdClass);
        $this->assertSame(array(), $result);
    }
} 