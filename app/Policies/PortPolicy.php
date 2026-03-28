<?php

namespace App\Policies;

use App\Facades\Permissions;
use App\Models\Port;
use App\Models\User;

class PortPolicy
{
    use ChecksGlobalPermissions;

    /**
     * Determine whether the user can view any ports.
     */
    public function viewAny(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'view')
            || $this->hasGlobalPermission($user, 'viewAll')
            || $this->hasGlobalPermission($user, 'update')
            || $this->hasGlobalPermission($user, 'delete');
    }

    /**
     * Determine whether the user can view all models.
     */
    public function viewAll(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'viewAll');
    }

    /**
     * Determine whether the user can view the port.
     */
    public function view(User $user, Port $port): bool
    {
        if ($this->hasGlobalPermission($user, 'viewAll')) {
            return true;
        }

        return $this->hasGlobalPermission($user, 'view')
            || Permissions::canAccessDevice($port->device_id, $user)
            || Permissions::canAccessPort($port, $user);
    }

    /**
     * Determine whether the user can update the port.
     */
    public function update(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'update');
    }

    /**
     * Determine whether the user can delete the port.
     */
    public function delete(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'delete');
    }

    /**
     * Determine whether the user can restore the port.
     */
    public function restore(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'restore');
    }

    /**
     * Determine whether the user can permanently delete the port.
     */
    public function forceDelete(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'forceDelete');
    }
}
