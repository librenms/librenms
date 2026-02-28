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
     */
    public function view(User $user, User $target): bool
    {
        return $user->is($target) // allow users to view themselves
            || $this->hasGlobalPermission($user, 'view');
    }

    /**
     * Determine whether the user can create users.
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
     */
    public function update(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'update');
    }

    /**
     * Determine whether the user can delete the user.
     */
    public function delete(User $user, User $target): bool
    {
        // do not allow users to delete themselves
        if ($user->is($target)) {
            return false;
        }

        return $this->hasGlobalPermission($user, 'delete');
    }
}
