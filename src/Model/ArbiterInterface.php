<?php

namespace ProgrammingAreHard\Arbiter\Model;

use Symfony\Component\Security\Core\User\UserInterface;

interface ArbiterInterface extends PermissionsFactoryInterface
{
    /**
     * Get the user's permissions.
     *
     * @param UserInterface $user
     * @return PermissionsInterface
     */
    public function getPermissions(UserInterface $user);

    /**
     * Grant the permissions.
     *
     * @param UserInterface $user
     */
    public function updatePermissions(UserInterface $user, PermissionsInterface $permissions);

    /**
     * Check if a user has permissions.
     *
     * @param UserInterface $user
     * @return bool
     */
    public function isGranted(UserInterface $user, PermissionsInterface $permissions);
}