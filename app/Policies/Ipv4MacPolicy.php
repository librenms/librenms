<?php

namespace App\Policies;

use App\Facades\Permissions;
use App\Models\Ipv4Mac;
use App\Models\User;

class Ipv4MacPolicy
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
    public function view(User $user, Ipv4Mac $ipv4Mac): bool
    {
        if ($this->hasGlobalPermission($user, 'viewAll')) {
            return true;
        }

        return $this->hasGlobalPermission($user, 'view', true)
            && (Permissions::canAccessPort($ipv4Mac->port_id, $user)
                || ($ipv4Mac->port !== null && Permissions::canAccessDevice($ipv4Mac->port->device_id, $user)));
    }
}
