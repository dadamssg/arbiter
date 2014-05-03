<?php

namespace ProgrammingAreHard\Arbiter\Domain;

use ProgrammingAreHard\Arbiter\Model\PermissionsMaskAggregatorInterface;
use ProgrammingAreHard\Arbiter\Model\PermissionMapInterface;
use string;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;

class PermissionsMaskAggregator implements PermissionsMaskAggregatorInterface
{
    /**
     * Permissions.
     *
     * @var string[]
     */
    private $permissions = array();

    /**
     * Aggregation mode.
     *
     * @var int
     */
    private $mode = self::MASK_ADD;

    /**
     * Mask builder.
     *
     * @var MaskBuilder
     */
    private $maskBuilder;

    /**
     * Permission map.
     *
     * @var PermissionMapInterface
     */
    private $permissionMap;

    /**
     * Constructor.
     *
     * @param MaskBuilder $maskBuilder
     * @param PermissionMapInterface $permissionMap
     */
    public function __construct(MaskBuilder $maskBuilder = null, PermissionMapInterface $permissionMap = null)
    {
        $this->maskBuilder = $maskBuilder ? : new MaskBuilder;
        $this->permissionMap = $permissionMap ? : new PermissionMap;
    }

    /**
     * {@inheritdoc}
     */
    public function setPermissions($permissions)
    {
        $this->permissions = [];

        $permissions = $this->makeArray($permissions);

        $this->addPermissions($permissions);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addPermissions($permissions)
    {
        $permissions = $this->makeArray($permissions);

        foreach ($permissions as $permission) {
            $this->addPermission($permission);
        }

        return $this;
    }

    /**
     * Add a single permission.
     *
     * @param string $permission
     * @return $this
     * @throws \InvalidArgumentException
     */
    protected function addPermission($permission)
    {
        if (!$this->permissionMap->contains($permission)) {
            throw new \InvalidArgumentException(sprintf('Unsupported permission: %s', $permission));
        }

        $this->permissions[] = $permission;

        return $this;
    }

    /**
     * Remove permissions.
     *
     * @param string[] $permssions
     * @return $this
     */
    public function removePermissions($permssions)
    {
        // TODO: Implement removePermissions() method.
    }

    /**
     * {@inheritdoc}
     */
    public function setMode($mode = self::MASK_ADD)
    {
        if (self::MASK_ADD !== $mode && self::MASK_REMOVE !== $mode) {
            throw new \InvalidArgumentException(sprintf(
                'Unsupported permission build mode. Supported modes: %1$s::MASK_ADD, %1$s::MASK_REMOVE',
                get_class($this)
            ));
        }

        $this->mode = $mode;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function build($initialMask = 0)
    {
        $this->maskBuilder->reset();

        $existingPermissions = $this->permissionMap->maskToPermissions($initialMask);

        if (self::MASK_ADD == $this->mode) {
            $permissions = array_merge($existingPermissions, $this->permissions);
        } else {
            $permissions = array_diff($existingPermissions, $this->permissions);
        }

        $masks = $this->permissionMap->permissionsToMasks($permissions);

        $masks = array_unique($masks);

        foreach ($masks as $mask) {
            $this->maskBuilder->add($mask);
        }

        return $this->maskBuilder->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getMasks($object)
    {
        return $this->permissionMap->permissionsToMasks($this->permissions);
    }

    /**
     * Make the permissions an array if not already.
     *
     * @param string|string[] $permissions
     * @return array
     */
    private function makeArray($permissions)
    {
        return is_array($permissions) ? $permissions : (array)$permissions;
    }
}