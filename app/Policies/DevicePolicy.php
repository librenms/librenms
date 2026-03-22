<?php

namespace App\Policies;

use App\Facades\Permissions;
use App\Models\Device;
use App\Models\User;

class DevicePolicy
{
    use ChecksGlobalPermissions;

    /**
     * Determine whether the user can view any devices.
     *
     * @param  User  $user
     */
    public function viewAny(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'viewAny');
    }

    /**
     * Determine whether the user can view the device.
     */
    public function view(User $user, Device|int $device): bool
    {
        if ($this->hasGlobalPermission($user, 'viewAny')) {
            return true;
        }

        $device_id = $device->device_id ?? $device['device_id'];

        return $this->hasGlobalPermission($user, 'view')
            || Permissions::canAccessDevice($device_id, $user);
    }

    /**
     * Determine whether the user can create devices.
     *
     * @param  User  $user
     */
    public function create(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'create');
    }

    /**
     * Determine whether the user can update the device.
     */
    public function update(User $user, Device|array $device): bool
    {
        if ($this->hasGlobalPermission($user, 'update') && $this->hasGlobalPermission($user, 'viewAny')) {
            return true;
        }

        $device_id = $device->device_id ?? $device['device_id'];

        return $this->hasGlobalPermission($user, 'update') &&
            Permissions::canAccessDevice($device_id, $user);
    }

    public function canDelete(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'delete');
    }
    
    /**
     * Determine whether the user can delete the device.
     */
    public function delete(User $user, Device|array $device): bool
    {
        if ($this->hasGlobalPermission($user, 'delete') && $this->hasGlobalPermission($user, 'viewAny')) {
            return true;
        }

        $device_id = $device->device_id ?? $device['device_id'];

        return $this->hasGlobalPermission($user, 'delete') &&
            Permissions::canAccessDevice($device_id, $user);
    }

    /**
     * Determine whether the user can view the stored configuration of the device
     * from Oxidized or Rancid
     */
    public function showConfig(User $user, Device|array $device): bool
    {
        if ($this->hasGlobalPermission($user, 'showConfig') && $this->hasGlobalPermission($user, 'viewAny')) {
            return true;
        }

        $device_id = $device->device_id ?? $device['device_id'];

        return $this->hasGlobalPermission($user, 'showConfig') &&
            Permissions::canAccessDevice($device_id, $user);
    }

    /**
     * Determine whether the user can update device notes.
     */
    public function updateNotes(User $user, Device|array $device): bool
    {
        if ($this->hasGlobalPermission($user, 'updateNotes') && $this->hasGlobalPermission($user, 'viewAny')) {
            return true;
        }

        $device_id = $device->device_id ?? $device['device_id'];

        return $this->hasGlobalPermission($user, 'updateNotes') &&
            Permissions::canAccessDevice($device_id, $user);
    }
}
