<?php

namespace App\Policies;

use App\Facades\Permissions;
use App\Models\PortStatistic;
use App\Models\User;

class PortStatisticPolicy
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
    public function view(User $user, PortStatistic $portStatistic): bool
    {
        if ($this->hasGlobalPermission($user, 'viewAll')) {
            return true;
        }

        return $this->hasGlobalPermission($user, 'view', true)
            && (Permissions::canAccessPort($portStatistic->port_id, $user)
                || ($portStatistic->port !== null && Permissions::canAccessDevice($portStatistic->port->device_id, $user)));
    }
}
