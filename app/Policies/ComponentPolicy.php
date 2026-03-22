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
        return $this->hasGlobalPermission($user, 'viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Component|array $component): bool
    {
        if ($this->hasGlobalPermission($user, 'viewAny')) {
            return true;
        }
        $device_id = $component->device_id ?? $component['device_id'];

        return $this->hasGlobalPermission($user, 'view') &&
            Permissions::canAccessDevice($device_id, $user);
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
    public function update(User $user, Component|array $component): bool
    {
        if ($this->hasGlobalPermission($user, 'update') && $this->hasGlobalPermission($user, 'viewAny')) {
            return true;
        }

        $device_id = $component->device_id ?? $component['device_id'];

        return $this->hasGlobalPermission($user, 'update') &&
            Permissions::canAccessDevice($device_id, $user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Component|array $component): bool
    {
        if ($this->hasGlobalPermission($user, 'delete') && $this->hasGlobalPermission($user, 'viewAny')) {
            return true;
        }

        $device_id = $component->device_id ?? $component['device_id'];

        return $this->hasGlobalPermission($user, 'delete') &&
            Permissions::canAccessDevice($device_id, $user);
    }
}
