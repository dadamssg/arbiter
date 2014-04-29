<?php

namespace ProgrammingAreHard\Arbiter\Model;

use Symfony\Component\Security\Core\User\UserInterface;

interface PermissionsArbiterInterface
{
    /**
     * Set the resource.
     *
     * @param object $object
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setObject($object);

    /**
     * Set the permission to grant.
     *
     * @param string|string[] $permissions
     * @return $this
     */
    public function setPermissions($permissions);

    /**
     * Grant the permission.
     *
     * @param UserInterface $user
     */
    public function grant(UserInterface $user);

    /**
     * Revoke the permission.
     *
     * @param UserInterface $user
     */
    public function revoke(UserInterface $user);

    /**
     * Check if a user has a permission.
     *
     * @param UserInterface $user
     * @return bool
     */
    public function isGranted(UserInterface $user);
} 