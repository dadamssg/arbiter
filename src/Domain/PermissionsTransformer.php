<?php

namespace ProgrammingAreHard\Arbiter\Domain;

use ProgrammingAreHard\Arbiter\Model\PermissionMapInterface;
use ProgrammingAreHard\Arbiter\Model\PermissionsFactoryInterface;
use ProgrammingAreHard\Arbiter\Model\PermissionsInterface;
use ProgrammingAreHard\Arbiter\Model\PermissionsTransformerInterface;
use Symfony\Component\Security\Acl\Permission\BasicPermissionMap;

class PermissionsTransformer implements PermissionsTransformerInterface
{
    /**
     * @var PermissionMapInterface
     */
    protected $map;

    /**
     * @var PermissionsFactoryInterface
     */
    protected $permissionsFactory;

    /**
     * Constructor.
     *
     * @param PermissionMapInterface $map
     * @param PermissionsFactoryInterface $permissionsFactory
     */
    public function __construct(
        PermissionMapInterface $map = null,
        PermissionsFactoryInterface $permissionsFactory = null
    ) {
        $this->map = $map ? : new PermissionMap;
        $this->permissionsFactory = $permissionsFactory ? : new PermissionsFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function permissionsToMask(PermissionsInterface $permissions)
    {
        $mask = 0;

        foreach ($permissions as $permission) {

            $this->ensureValidPermission($permission);

            $mask |= $this->map->getMask($permission);
        }

        return $mask;
    }

    /**
     * {@inheritdoc}
     */
    public function permissionsToMasks(PermissionsInterface $permissions)
    {
        $masks = array();

        foreach ($permissions as $permission) {

            $this->ensureValidPermission($permission);

            $masks = array_merge($masks, $this->map->getMasks($permission));
        }

        return array_unique($masks);
    }

    /**
     * {@inheritdoc}
     */
    public function maskToPermissions($mask)
    {
        $permissions = array();

        foreach ($this->map as $permission => $val) {

            if ($mask & $val) {
                $permissions[] = $this->map->getPermission($val);
            }
        }

        return $this->permissionsFactory->newPermissions($permissions);
    }

    /**
     * {@inheritdoc}
     */
    public function newPermissions(array $permissions = array())
    {
        foreach ($permissions as $permission) {
            $this->ensureValidPermission($permission);
        }

        return $this->permissionsFactory->newPermissions($permissions);
    }

    /**
     * Check if valid permission.
     *
     * @param string $permission
     * @throws \InvalidArgumentException
     */
    protected function ensureValidPermission($permission)
    {
        if (!$this->map->supportsPermission($permission)) {
            throw new \InvalidArgumentException(sprintf('Unsupported permission: %s', $permission));
        }
    }
}