<?php

namespace ProgrammingAreHard\Arbiter\Model;

interface MasksAggregatorInterface
{
    const MASK_ADD = 1;
    const MASK_REMOVE = 0;

    /**
     * Set the permissions.
     *
     * @param string|string[] $permissions
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setPermissions($permissions);

    /**
     * Set the aggregation mode.
     *
     * @param int $mode
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setMode($mode = self::MASK_ADD);

    /**
     * Build the mask.
     *
     * @param int $initialMask
     * @return int
     */
    public function build($initialMask = 0);

    /**
     * Get all the masks represented by the permissions.
     *
     * @param object $object
     * @return int[]
     */
    public function getAllMasks($object);
} 