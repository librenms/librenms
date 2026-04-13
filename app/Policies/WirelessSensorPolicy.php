<?php

namespace App\Policies;

use App\Facades\Permissions;
use App\Models\User;
use App\Models\WirelessSensor;

class WirelessSensorPolicy
{
    use ChecksGlobalPermissions;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'update')
            || $this->hasGlobalPermission($user, 'delete');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, WirelessSensor $wirelessSensor): bool
    {
        return Permissions::canAccessDevice($wirelessSensor->device_id, $user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'update');
    }
}
