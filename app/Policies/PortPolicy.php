<?php

namespace App\Policies;

use App\Facades\Permissions;
use App\Models\Port;
use App\Models\PortGroup;
use App\Models\User;
use Illuminate\Support\Collection;

class PortPolicy
{
    use ChecksGlobalPermissions;

    /**
     * Determine whether the user can view any ports.
     */
    public function viewAny(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'view', true)
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

        return $this->hasGlobalPermission($user, 'view', true)
            && (Permissions::canAccessDevice($port->device_id, $user)
                || Permissions::canAccessPort($port, $user));
    }

    /**
     * Determine whether the user can update the port.
     */
    public function update(User $user, Port $port): bool
    {
        return $this->hasGlobalPermission($user, 'update')
            && (Permissions::canAccessDevice($port->device_id, $user)
                || Permissions::canAccessPort($port, $user));
    }

    /**
     * Determine whether the user can delete the port.
     */
    public function delete(User $user, Port $port): bool
    {
        return $this->hasGlobalPermission($user, 'delete')
            && (Permissions::canAccessDevice($port->device_id, $user)
                || Permissions::canAccessPort($port, $user));
    }

    public function attachGroups(User $user, Port $port, PortGroup $group): bool
    {
        return $this->update($user, $port);
    }

    public function syncGroups(User $user, Port $port, Collection $groups): bool
    {
        return $this->update($user, $port);
    }

    public function detachGroups(User $user, Port $port, PortGroup $group): bool
    {
        return $this->update($user, $port);
    }
}
