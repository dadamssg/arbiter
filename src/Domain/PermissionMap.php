<?php

namespace ProgrammingAreHard\Arbiter\Domain;

use ProgrammingAreHard\Arbiter\Model\PermissionMapInterface;
use Symfony\Component\Security\Acl\Permission\BasicPermissionMap;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;

class PermissionMap implements PermissionMapInterface
{
    /**
     * Permission => mask map
     *
     * @var array
     */
    protected $permissionMap = array(
        BasicPermissionMap::PERMISSION_VIEW => MaskBuilder::MASK_VIEW,
        BasicPermissionMap::PERMISSION_EDIT => MaskBuilder::MASK_EDIT,
        BasicPermissionMap::PERMISSION_CREATE => MaskBuilder::MASK_CREATE,
        BasicPermissionMap::PERMISSION_DELETE => MaskBuilder::MASK_DELETE,
        BasicPermissionMap::PERMISSION_UNDELETE => MaskBuilder::MASK_UNDELETE,
        BasicPermissionMap::PERMISSION_OPERATOR => MaskBuilder::MASK_OPERATOR,
        BasicPermissionMap::PERMISSION_MASTER => MaskBuilder::MASK_MASTER,
        BasicPermissionMap::PERMISSION_OWNER => MaskBuilder::CODE_OWNER
    );

    /**
     * {@inheritdoc}
     */
    public function contains($permission)
    {
        return array_key_exists($this->normalizePermission($permission), $this->permissionMap);
    }

    /**
     * {@inheritdoc}
     */
    public function maskToPermissions($mask)
    {
        $permissions = array();

        foreach ($this->permissionMap as $permission => $val) {

            if ($mask & $val) {
                $permissions[$val] = $this->normalizePermission($permission);
            }
        }

        ksort($permissions);

        return array_values($permissions);
    }

    /**
     * {@inheritdoc}
     */
    public function permissionsToMasks(array $permissions)
    {
        $masks = array();

        foreach ($permissions as $permission) {
            $permission = $this->normalizePermission($permission);
            if (array_key_exists($permission, $this->permissionMap)) {
                $masks[] = $this->permissionMap[$permission];
            }
        }

        return $masks;
    }

    /**
     * Normalize the permission.
     *
     * @param string $permission
     * @return string
     */
    protected function normalizePermission($permission)
    {
        return strtoupper($permission);
    }
} 