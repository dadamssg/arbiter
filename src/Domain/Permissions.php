<?php

namespace ProgrammingAreHard\Arbiter\Domain;

use ProgrammingAreHard\Arbiter\Model\PermissionsInterface;

class Permissions implements PermissionsInterface
{
    /**
     * Permissions.
     *
     * @var string[]
     */
    protected $permissions = array();

    /**
     * Constructor.
     *
     * @param string[] $permissions
     */
    public function __construct(array $permissions = array())
    {
        foreach ($permissions as $permission) {
            $this->add($permission);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function add($permission)
    {
        if (!$this->contains($permission)) {
            $this->permissions[] = $permission;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($permission)
    {
        if ($this->contains($permission)) {
            if ($key = array_search($permission, $this->permissions)) {
                unset($this->permissions[$key]);
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function contains($permission)
    {
        return in_array($permission, $this->permissions);
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return $this->permissions;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->permissions);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->permissions);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return implode(',', $this->permissions);
    }
}