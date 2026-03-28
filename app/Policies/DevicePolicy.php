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
        return $this->hasGlobalPermission($user, 'view')
            || $this->hasGlobalPermission($user, 'viewAll')
            || $this->hasGlobalPermission($user, 'create')
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
     * Determine whether the user can view the device.
     */
    public function view(User $user, Device $device): bool
    {
        if ($this->hasGlobalPermission($user, 'viewAll')) {
            return true;
        }

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
     */
    public function update(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'update');
    }

    /**
     * Determine whether the user can delete the device.
     */
    public function delete(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'delete');
    }

    /**
     * Determine whether the user can view the stored configuration of the device
     * from Oxidized or Rancid
     */
    public function showConfig(User $user, Device $device): bool
    {
        return $this->hasGlobalPermission($user, 'showConfig')
            && $this->view($user, $device);
    }

    /**
     * Determine whether the user can update device notes.
     */
    public function updateNotes(User $user, Device $device): bool
    {
        return $this->hasGlobalPermission($user, 'updateNotes')
            && $this->view($user, $device);
    }
}
