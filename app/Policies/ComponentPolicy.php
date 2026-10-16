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
        return $this->hasGlobalPermission($user, 'view', true)
            || $this->hasGlobalPermission($user, 'viewAll')
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

        return $this->hasGlobalPermission($user, 'view', true)
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
     * Without a component, only the global permission is checked.
     */
    public function update(User $user, ?Component $component = null): bool
    {
        return $this->hasGlobalPermission($user, 'update')
            && ($component === null || $this->view($user, $component));
    }

    /**
     * Determine whether the user can delete the model.
     * Without a component, only the global permission is checked.
     */
    public function delete(User $user, ?Component $component = null): bool
    {
        return $this->hasGlobalPermission($user, 'delete')
            && ($component === null || $this->view($user, $component));
    }
}
