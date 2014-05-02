<?php

namespace ProgrammingAreHard\Arbiter\Model;

interface PermissionMapInterface
{
    /**
     * Whether this map contains the given permission.
     *
     * @param string $permission
     * @return bool
     */
    public function contains($permission);

    /**
     * Transform a bitmask to an array of permissions.
     *
     * @param int $mask
     * @return string[]
     */
    public function maskToPermissions($mask);

    /**
     * Transform an array of permissions to bitmasks
     *
     * @param string[] $permissions
     * @return int[]
     */
    public function permissionsToMasks(array $permissions);
} 