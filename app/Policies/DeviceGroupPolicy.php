<?php

namespace App\Policies;

use App\Facades\Permissions;
use App\Models\Device;
use App\Models\DeviceGroup;
use App\Models\ServiceTemplate;
use App\Models\User;
use Illuminate\Support\Collection;

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

    /**
     * Restify v10.4 attach gate: POST /api/v1/device-groups/{id}/attach/devices
     */
    public function attachDevices(User $user, DeviceGroup $deviceGroup, Device $device): bool
    {
        return $this->update($user, $deviceGroup);
    }

    /**
     * Restify v10.4 sync gate: POST /api/v1/device-groups/{id}/sync/devices
     */
    public function syncDevices(User $user, DeviceGroup $deviceGroup, Collection $devices): bool
    {
        return $this->update($user, $deviceGroup);
    }

    /**
     * Restify v10.4 detach gate: DELETE /api/v1/device-groups/{id}/detach/devices
     */
    public function detachDevices(User $user, DeviceGroup $deviceGroup, Device $device): bool
    {
        return $this->update($user, $deviceGroup);
    }

    public function attachUsers(User $user, DeviceGroup $deviceGroup, User $target): bool
    {
        return $this->update($user, $deviceGroup);
    }

    public function syncUsers(User $user, DeviceGroup $deviceGroup, Collection $users): bool
    {
        return $this->update($user, $deviceGroup);
    }

    public function detachUsers(User $user, DeviceGroup $deviceGroup, User $target): bool
    {
        return $this->update($user, $deviceGroup);
    }
}
