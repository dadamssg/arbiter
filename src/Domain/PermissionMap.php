<?php

namespace ProgrammingAreHard\Arbiter\Domain;

use ProgrammingAreHard\Arbiter\Model\PermissionMapInterface;
use Symfony\Component\Security\Acl\Permission\BasicPermissionMap;
use Symfony\Component\Security\Acl\Permission\PermissionMapInterface as BasicMapInterface;

use Symfony\Component\Security\Acl\Permission\MaskBuilder;

class PermissionMap implements PermissionMapInterface
{
    /**
     * @var BasicMapInterface
     */
    protected $basicMap;

    /**
     * Permission => mask map
     *
     * @var array
     */
    protected $map = array(
        BasicPermissionMap::PERMISSION_VIEW => MaskBuilder::MASK_VIEW,
        BasicPermissionMap::PERMISSION_EDIT => MaskBuilder::MASK_EDIT,
        BasicPermissionMap::PERMISSION_CREATE => MaskBuilder::MASK_CREATE,
        BasicPermissionMap::PERMISSION_DELETE => MaskBuilder::MASK_DELETE,
        BasicPermissionMap::PERMISSION_UNDELETE => MaskBuilder::MASK_UNDELETE,
        BasicPermissionMap::PERMISSION_OPERATOR => MaskBuilder::MASK_OPERATOR,
        BasicPermissionMap::PERMISSION_MASTER => MaskBuilder::MASK_MASTER,
        BasicPermissionMap::PERMISSION_OWNER => MaskBuilder::MASK_OWNER
    );

    /**
     * Constructor.
     *
     * @param BasicMapInterface $basicMap
     */
    public function __construct(BasicMapInterface $basicMap)
    {
        $this->basicMap = $basicMap;
    }

    /**
     * {@inheritdoc}
     */
    public function getMask($permission)
    {
        return $this->map[$permission];
    }

    /**
     * {@inheritdoc}
     */
    public function getMasks($permission)
    {
        return $this->basicMap->getMasks($permission, $permission);
    }

    /**
     * {@inheritdoc}
     */
    public function getPermission($mask)
    {
        return array_search($mask, $this->map);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsPermission($permission)
    {
        return array_key_exists($permission, $this->map);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->map);
    }
} 