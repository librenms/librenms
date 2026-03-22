<?php

namespace App\Policies;

use App\Models\User;

class CustomMapPolicy
{
    use ChecksGlobalPermissions;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, int $customMapId): bool
    {
        if ($this->hasGlobalPermission($user, 'viewAny')) {
            return true;
        }

        return $this->hasGlobalPermission($user, 'view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, int $customMapId): bool
    {
        return $this->hasGlobalPermission($user, 'update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, int $customMapId): bool
    {
        return $this->hasGlobalPermission($user, 'delete');
    }
}
