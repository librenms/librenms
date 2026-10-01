<?php

namespace App\Policies;

use App\Facades\Permissions;
use App\Models\Customoid;
use App\Models\User;

class CustomoidPolicy
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
    public function view(User $user, Customoid $customoid): bool
    {
        return $this->hasGlobalPermission($user, 'view', true)
            && Permissions::canAccessDevice($customoid->device_id, $user);
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
    public function update(User $user, Customoid $customoid): bool
    {
        return $this->hasGlobalPermission($user, 'update')
            && Permissions::canAccessDevice($customoid->device_id, $user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Customoid $customoid): bool
    {
        return $this->hasGlobalPermission($user, 'delete')
            && Permissions::canAccessDevice($customoid->device_id, $user);
    }
}
