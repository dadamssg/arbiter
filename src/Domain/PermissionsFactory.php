<?php

namespace ProgrammingAreHard\Arbiter\Domain;

use ProgrammingAreHard\Arbiter\Model\PermissionsFactoryInterface;

class PermissionsFactory implements PermissionsFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function newPermissions(array $permissions = array())
    {
        return new Permissions($permissions);
    }
} 