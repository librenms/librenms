<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the user.
     *
     * @param  User  $user
     * @param  User  $target
     */
    public function view(User $user, User $target): ?bool
    {
        return $target->is($user) ?: null;  // allow users to view themselves
    }

    /**
     * Determine whether the user can create users.
     *
     * @param  User  $user
     */
    public function create(User $user): ?bool
    {
        // if not mysql, forbid, otherwise defer to bouncer
        if (\LibreNMS\Config::get('auth_mechanism') != 'mysql') {
            return false;
        }

        return null;
    }

    /**
     * Determine whether the user can update the user.
     *
     * @param  User  $user
     * @param  User  $target
     */
    public function update(User $user, User $target = null): ?bool
    {
        if ($target == null) {
            return null;
        }

        return $target->is($user) ?: null; // allow user to update self or defer to bouncer
    }

    /**
     * Determine whether the user can delete the user.
     *
     * @param  User  $user
     * @param  User  $target
     */
    public function delete(User $user, User $target): ?bool
    {
        return $target->is($user) ? false : null; // do not allow users to delete themselves or defer to bouncer
    }
}
