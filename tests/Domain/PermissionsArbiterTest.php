<?php

namespace ProgrammingAreHard\Arbiter\Tests\Domain;

use ProgrammingAreHard\Arbiter\Domain\PermissionsArbiter;

class PermissionsArbiterTest extends \PHPUnit_Framework_TestCase
{
    private $arbiter;

    private $aclProvider;

    private $masksAggregator;

    public function setUp()
    {
        $this->arbiter = new PermissionsArbiter(
            $this->aclProvider = $this->getMock('Symfony\Component\Security\Acl\Model\MutableAclProviderInterface'),
            $this->masksAggregator = $this->getMock('ProgrammingAreHard\Arbiter\Model\MasksAggregatorInterface')
        );
    }

    public function testSettingObjectReturnsSelf()
    {
        $result = $this->arbiter->setObject(new \stdClass);
        $this->assertSame($this->arbiter, $result);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSettingNotObjectThrowsException()
    {
        $this->arbiter->setObject('foobar');
    }

    public function testSettingPermissionsSetsOnMasksAggretaor()
    {
        $permissions = array('VIEW', 'EDIT');

        $this->masksAggregator
        ->expects($this->once())
            ->method('setPermissions')
            ->with($this->equalTo($permissions));

        $result = $this->arbiter->setPermissions($permissions);
        $this->assertSame($this->arbiter, $result);
    }
} 