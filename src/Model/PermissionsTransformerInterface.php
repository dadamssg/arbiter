<?php
namespace ProgrammingAreHard\Arbiter\Model;

interface PermissionsTransformerInterface
{
    /**
     * Convert permissions to a bitmask.
     *
     * @param PermissionsInterface $permissions
     * @return int
     * @throws \InvalidArgumentException
     */
    public function permissionsToMask(PermissionsInterface $permissions);

    /**
     * Convert the permissions to an array of bitmasks.
     *
     * @param PermissionsInterface $permissions
     * @return int[]
     * @throws \InvalidArgumentException
     */
    public function permissionsToMasks(PermissionsInterface $permissions);

    /**
     * Conver the bitmask to permissions.
     *
     * @param int $mask
     * @return PermissionsInterface
     */
    public function maskToPermissions($mask);

    /**
     * Transform array to permissions.
     *
     * @param string[] $permissions
     * @return PermissionsInterface
     */
    public function arrayToPermissions(array $permissions = array());
}