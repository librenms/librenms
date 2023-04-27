<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can manage users.
     *
     * @param  User  $user
     */
    public function manage(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view the user.
     *
     * @param  User  $user
     * @param  User  $target
     */
    public function view(User $user, User $target): bool
    {
        return $user->isAdmin() || $target->is($user);
    }

    /**
     * Determine whether the user can view any user.
     *
     * @param  User  $user
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can create users.
     *
     * @param  User  $user
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the user.
     *
     * @param  User  $user
     * @param  User  $target
     */
    public function update(User $user, User $target): bool
    {
        return $user->isAdmin() || $target->is($user);
    }

    /**
     * Determine whether the user can delete the user.
     *
     * @param  User  $user
     * @param  User  $target
     */
    public function delete(User $user, User $target): bool
    {
        return $user->isAdmin();
    }
}
