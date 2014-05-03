<?php

namespace ProgrammingAreHard\Arbiter\Model;

interface PermissionsMaskAggregatorInterface
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
     * Add permissions.
     *
     * @param string[] $permissions
     * @return $this
     */
    public function addPermissions($permissions);

    /**
     * Remove permissions.
     *
     * @param string[] $permssions
     * @return $this
     */
    public function removePermissions($permssions);

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
    public function getMasks($object);
} 