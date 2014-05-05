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
    public function toMask(PermissionsInterface $permissions);

    /**
     * Convert the permissions to an array of bitmasks.
     *
     * @param PermissionsInterface $permissions
     * @return int[]
     * @throws \InvalidArgumentException
     */
    public function toMasks(PermissionsInterface $permissions);

    /**
     * Conver the bitmask to permissions.
     *
     * @param int $mask
     * @return PermissionsInterface
     */
    public function toPermissions($mask);
}