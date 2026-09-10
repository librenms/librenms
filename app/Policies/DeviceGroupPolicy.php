<?php

namespace App\Policies;

use App\Facades\Permissions;
use App\Models\DeviceGroup;
use App\Models\User;

class DeviceGroupPolicy
{
    use ChecksGlobalPermissions;

    /**
     * Determine whether the user can view any device group.
     */
    public function viewAny(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'view', true)
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
     * Determine whether the user can view the device group.
     */
    public function view(User $user, DeviceGroup $deviceGroup): bool
    {
        if ($this->hasGlobalPermission($user, 'viewAll')) {
            return true;
        }

        return $this->hasGlobalPermission($user, 'view', true)
            && Permissions::canAccessDeviceGroup($deviceGroup, $user);
    }

    /**
     * Determine whether the user can create device groups.
     */
    public function create(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'create');
    }

    /**
     * Determine whether the user can update the device group.
     */
    public function update(User $user, DeviceGroup $deviceGroup): bool
    {
        return $this->hasGlobalPermission($user, 'update')
            && Permissions::canAccessDeviceGroup($deviceGroup, $user);
    }

    /**
     * Determine whether the user can delete the device group.
     */
    public function delete(User $user, DeviceGroup $deviceGroup): bool
    {
        return $this->hasGlobalPermission($user, 'delete')
            && Permissions::canAccessDeviceGroup($deviceGroup, $user);
    }
}
