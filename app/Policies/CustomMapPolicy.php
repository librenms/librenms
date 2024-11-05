<?php

namespace App\Policies;

use App\Models\CustomMap;
use App\Models\User;

class CustomMapPolicy
{
    public function before(User $user): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasGlobalRead();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CustomMap $customMap): bool
    {
        return $user->hasGlobalRead() || $customMap->hasReadAccess($user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CustomMap $customMap): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CustomMap $customMap): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CustomMap $customMap): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CustomMap $customMap): bool
    {
        return false;
    }
}
