<?php

namespace App\Policies;

use App\Facades\Permissions;
use App\Models\Ipv4Address;
use App\Models\User;

class Ipv4AddressPolicy
{
    use ChecksGlobalPermissions;

    public function __construct()
    {
        $this->globalPrefix = 'port';
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'view', true)
            || $this->hasGlobalPermission($user, 'viewAll');
    }

    /**
     * Determine whether the user can view all models.
     */
    public function viewAll(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'viewAll');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Ipv4Address $ipv4Address): bool
    {
        if ($this->hasGlobalPermission($user, 'viewAll')) {
            return true;
        }

        return $this->hasGlobalPermission($user, 'view', true)
            && (Permissions::canAccessPort($ipv4Address->port_id, $user)
                || ($ipv4Address->port !== null && Permissions::canAccessDevice($ipv4Address->port->device_id, $user)));
    }
}
