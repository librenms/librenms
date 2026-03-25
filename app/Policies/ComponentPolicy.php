<?php

namespace App\Policies;

use App\Facades\Permissions;
use App\Models\Component;
use App\Models\User;

class ComponentPolicy
{
    use ChecksGlobalPermissions;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'view')
            || $this->hasGlobalPermission($user, 'create')
            || $this->hasGlobalPermission($user, 'update')
            || $this->hasGlobalPermission($user, 'delete');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Component $component): bool
    {
        if ($this->hasGlobalPermission($user, 'viewAll')) {
            return true;
        }

        return $this->hasGlobalPermission($user, 'view')
            && Permissions::canAccessDevice($component->device_id, $user);
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
    public function update(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'delete');
    }
}
