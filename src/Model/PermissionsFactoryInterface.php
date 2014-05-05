<?php

namespace ProgrammingAreHard\Arbiter\Model;

interface PermissionsFactoryInterface
{
    /**
     * Create new permissions.
     *
     * @param string[] $permissions
     * @return PermissionsInterface
     */
    public function newPermissions(array $permissions = array());
}