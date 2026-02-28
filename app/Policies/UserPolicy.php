<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    use ChecksGlobalPermissions;

    public function viewAny(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'viewAny');
    }

    /**
     * Determine whether the user can view the user.
     *
     * @param  User  $user
     * @param  User  $target
     */
    public function view(User $user, User $target): bool
    {
        return $target->is($user) // allow users to view themselves
            || $this->hasGlobalPermission($user, 'view');
    }

    /**
     * Determine whether the user can create users.
     *
     * @param  User  $user
     */
    public function create(User $user): bool
    {
        // if not mysql, forbid
        if (\App\Facades\LibrenmsConfig::get('auth_mechanism') != 'mysql') {
            return false;
        }

        return $this->hasGlobalPermission($user, 'create');
    }

    /**
     * Determine whether the user can update the user.
     *
     * @param  User  $user
     * @param  User  $target
     */
    public function update(User $user, ?User $target = null): bool
    {
        return $this->hasGlobalPermission($user, 'update');
    }

    public function updatePassword(User $user, User $target): bool
    {
        return $target->is($user)
            || $this->hasGlobalPermission($user, 'update');
    }

    /**
     * Determine whether the user can delete the user.
     *
     * @param  User  $user
     * @param  User  $target
     */
    public function delete(User $user, User $target): bool
    {
        // do not allow users to delete themselves
        if ($target->is($user)) {
            return false;
        }

        return $this->hasGlobalPermission($user, 'delete');
    }
}
