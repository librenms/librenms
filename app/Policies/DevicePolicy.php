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
     *
     * @param  User  $user
     * @param  Device  $device
     */
    public function view(User $user, Device $device): bool
    {
        return $this->hasGlobalPermission($user, 'view')
            || Permissions::canAccessDevice($device, $user);
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
     *
     * @param  User  $user
     * @param  Device  $device
     */
    public function update(User $user, Device $device): bool
    {
        return $this->hasGlobalPermission($user, 'update');
    }

    /**
     * Determine whether the user can delete the device.
     *
     * @param  User  $user
     * @param  Device  $device
     */
    public function delete(User $user, Device $device): bool
    {
        return $this->hasGlobalPermission($user, 'delete');
    }

    /**
     * Determine whether the user can view the stored configuration of the device
     * from Oxidized or Rancid
     *
     * @param  User  $user
     * @param  Device  $device
     */
    public function showConfig(User $user, Device $device): bool
    {
        return $this->hasGlobalPermission($user, 'showConfig');
    }

    /**
     * Determine whether the user can update device notes.
     *
     * @param  User  $user
     * @param  Device  $device
     */
    public function updateNotes(User $user, Device $device): bool
    {
        return $this->hasGlobalPermission($user, 'updateNotes');
    }
}
