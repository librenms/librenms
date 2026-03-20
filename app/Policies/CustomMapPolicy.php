<?php

namespace App\Policies;

use App\Models\CustomMap;
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
    public function view(User $user, CustomMap $customMap): bool
    {
        return $this->hasGlobalPermission($user, 'view')
            || $customMap->hasReadAccess($user);
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
    public function update(User $user, CustomMap $customMap): bool
    {
        return $this->hasGlobalPermission($user, 'update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CustomMap $customMap): bool
    {
        return $this->hasGlobalPermission($user, 'delete');
    }
}
