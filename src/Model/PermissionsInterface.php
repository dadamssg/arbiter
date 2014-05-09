<?php

namespace ProgrammingAreHard\Arbiter\Model;

interface PermissionsInterface extends \IteratorAggregate, \Countable
{
    /**
     * Does the permissions have the permission?
     *
     * @param string $permission
     * @return bool
     */
    public function contains($permission);

    /**
     * Add the permission.
     *
     * @param string $permission
     * @return $this
     */
    public function add($permission);

    /**
     * Remove the permission.
     *
     * @param string $permission
     * @return $this
     */
    public function remove($permission);

    /**
     * Convert to array.
     *
     * @return string[]
     */
    public function toArray();
}