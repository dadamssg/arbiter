<?php
/**
 * Created by PhpStorm.
 * User: davidadams
 * Date: 5/1/14
 * Time: 7:25 AM
 */

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

    public function setUp()
    {
        $this->aggregator = new PermissionsMaskAggregator;
    }

    /**
     * @test
     */
    public function it_can_remove_masks()
    {
//        $this->aggregator->setPermissions('OWNER');
//        $ownerMask = $this->aggregator->build();
//        $this->assertSame(128, $ownerMask);
//
//        $this->aggregator->setMode(MaskAggregator::MASK_REMOVE);
//
//        $this->aggregator->setPermissions('DELETE');
//
//        $resultMask = $this->aggregator->build($ownerMask);
//
//        $this->assertEquals('test', $resultMask);

        $builder = new MaskBuilder(128);
        $builder->remove('DELETE');

        $this->assertEquals('test', 'test');

    }
} 