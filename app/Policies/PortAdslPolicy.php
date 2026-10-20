<?php

namespace App\Policies;

use App\Facades\Permissions;
use App\Models\PortAdsl;
use App\Models\User;

class PortAdslPolicy
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
    public function view(User $user, PortAdsl $portAdsl): bool
    {
        if ($this->hasGlobalPermission($user, 'viewAll')) {
            return true;
        }

        return $this->hasGlobalPermission($user, 'view', true)
            && (Permissions::canAccessPort($portAdsl->port_id, $user)
                || ($portAdsl->port !== null && Permissions::canAccessDevice($portAdsl->port->device_id, $user)));
    }
}
