<?php

namespace ProgrammingAreHard\Arbiter\Domain;

use ProgrammingAreHard\Arbiter\Model\MasksAggregatorInterface;
use ProgrammingAreHard\Arbiter\Model\PermissionsArbiterInterface;
use string;
use Symfony\Component\Security\Core\User\UserInterface;

class PermissionsArbiter implements PermissionsArbiterInterface
{
    /**
     * Object to grant/check permissions against.
     *
     * @var object
     */
    private $object;

    /**
     * @var MasksAggregator
     */
    private $masksAggregator;

    public function __construct(MasksAggregatorInterface $maskAggregator = null)
    {
        $this->masksAggregator = $maskAggregator ? $maskAggregator : new MasksAggregator;
    }

    /**
     * {@inheritdoc}
     */
    public function setObject($object)
    {
        if (!is_object($object)) {
            throw new \InvalidArgumentException(sprintf(
                'PermissionsArbiter expected object. %s given',
                gettype($object)));
        }

        $this->object = $object;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setPermissions($permissions)
    {
        $this->masksAggregator->setPermissions($permissions);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function grant(UserInterface $user)
    {
        // TODO: Implement grant() method.
    }

    /**
     * {@inheritdoc}
     */
    public function revoke(UserInterface $user)
    {
        // TODO: Implement revoke() method.
    }

    /**
     * {@inheritdoc}
     */
    public function isGranted(UserInterface $user)
    {
        // TODO: Implement isGranted() method.
    }
} 