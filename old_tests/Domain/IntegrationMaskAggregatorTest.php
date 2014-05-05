<?php

namespace ProgrammingAreHard\Arbiter\Tests\Domain;


use ProgrammingAreHard\Arbiter\Domain\PermissionsMaskAggregator;
use ProgrammingAreHard\Arbiter\Model\PermissionsMaskAggregatorInterface;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;

class IntegrationMaskAggregatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PermissionsMaskAggregatorInterface
     */
    private $aggregator;

    /**
     * @var MaskBuilder
     */
    private $maskBuilder;

    public function setUp()
    {
        $this->aggregator = new PermissionsMaskAggregator;
        $this->maskBuilder = new MaskBuilder;
    }


    /**
     * @test
     */
    public function it_can_remove_masks()
    {
        $this->aggregator->setPermissions(array('VIEW', 'EDIT'));

        $viewEditMask = $this->aggregator->build();

        $this->assertEquals(MaskBuilder::MASK_VIEW | MaskBuilder::MASK_EDIT, $viewEditMask);

        $this->aggregator->setPermissions(array('EDIT'));

        $this->aggregator->setMode(PermissionsMaskAggregator::MASK_REMOVE);

        $result = $this->aggregator->build($viewEditMask);

        $editOnly = $this->maskBuilder->add('EDIT')->get();

        $this->assertTrue($editOnly & $result);

    }
} 