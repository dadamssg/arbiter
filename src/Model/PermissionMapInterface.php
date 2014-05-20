<?php

namespace ProgrammingAreHard\Arbiter\Model;

interface PermissionMapInterface extends \IteratorAggregate
{
    /**
     * Get the mask equivalent of the permission.
     *
     * @param string $permission
     * @return int
     * @throws \InvalidArgumentException
     */
    public function getMask($permission);

    /**
     * Get the masks associated with the permission.
     *
     * @param string $permission
     * @return int[]
     */
    public function getMasks($permission);

    /**
     * Get the permission equivalent of the mask.
     *
     * @param int $mask
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getPermission($mask);

    /**
     * Does the map support the permission?
     *
     * @param string $permission
     * @return bool
     */
    public function supportsPermission($permission);
} 