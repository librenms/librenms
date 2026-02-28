<?php

namespace App\Policies;

use App\Models\DeviceGroup;
use App\Models\User;

class DeviceGroupPolicy
{
    use ChecksGlobalPermissions;

    /**
     * Determine whether the user can view the device group.
     *
     * @param  User  $user
     * @param  DeviceGroup  $deviceGroup
     */
    public function view(User $user, DeviceGroup $deviceGroup): bool
    {
        return $this->hasGlobalPermission($user, 'view');
    }

    /**
     * Determine whether the user can view any device group.
     *
     * @param  User  $user
     */
    public function viewAny(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'viewAny');
    }

    /**
     * Determine whether the user can create device groups.
     *
     * @param  User  $user
     */
    public function create(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'create');
    }

    /**
     * Determine whether the user can update the device group.
     *
     * @param  User  $user
     * @param  DeviceGroup  $deviceGroup
     */
    public function update(User $user, DeviceGroup $deviceGroup): bool
    {
        return $this->hasGlobalPermission($user, 'update');
    }

    /**
     * Determine whether the user can delete the device group.
     *
     * @param  User  $user
     * @param  DeviceGroup  $deviceGroup
     */
    public function delete(User $user, DeviceGroup $deviceGroup): bool
    {
        return $this->hasGlobalPermission($user, 'delete');
    }
}
