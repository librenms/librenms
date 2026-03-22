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
        return $this->hasGlobalPermission($user, 'viewAny');
    }

    /**
     * Determine whether the user can view the port.
     */
    public function view(User $user, Port|array $port): bool
    {
        if ($this->hasGlobalPermission($user, 'viewAny')) {
            return true;
        }

        $port_id = $port->port_id ?? $port['port_id'];

        return $this->hasGlobalPermission($user, 'view')
            || Permissions::canAccessPort($port_id, $user);
    }

    /**
     * Determine whether the user can update the port.
     */
    public function update(User $user, Port|array $port): bool
    {
        if ($this->hasGlobalPermission($user, 'update') && $this->hasGlobalPermission($user, 'viewAny')) {
            return true;
        }

        $port_id = $port->port_id ?? $port['port_id'];

        return $this->hasGlobalPermission($user, 'update') &&
            Permissions::canAccessPort($port_id, $user);
    }

    /**
     * Determine whether the user can delete the port.
     */
    public function delete(User $user, Port|array $port): bool
    {
        if ($this->hasGlobalPermission($user, 'delete') && $this->hasGlobalPermission($user, 'viewAny')) {
            return true;
        }

        $port_id = $port->port_id ?? $port['port_id'];

        return $this->hasGlobalPermission($user, 'delete') &&
            Permissions::canAccessPort($port_id, $user);
    }

    /**
     * Determine whether the user can restore the port.
     */
    public function restore(User $user, Port|array $port): bool
    {
        if ($this->hasGlobalPermission($user, 'restore') && $this->hasGlobalPermission($user, 'viewAny')) {
            return true;
        }

        $port_id = $port->port_id ?? $port['port_id'];

        return $this->hasGlobalPermission($user, 'restore') &&
            Permissions::canAccessPort($port_id, $user);
    }

    /**
     * Determine whether the user can permanently delete the port.
     */
    public function forceDelete(User $user, Port|array $port): bool
    {
        if ($this->hasGlobalPermission($user, 'forceDelete') && $this->hasGlobalPermission($user, 'viewAny')) {
            return true;
        }

        $port_id = $port->port_id ?? $port['port_id'];

        return $this->hasGlobalPermission($user, 'forceDelete') &&
            Permissions::canAccessPort($port_id, $user);
    }
}
