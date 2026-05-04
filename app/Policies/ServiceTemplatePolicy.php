<?php

namespace App\Policies;

use App\Models\Device;
use App\Models\DeviceGroup;
use App\Models\ServiceTemplate;
use App\Models\User;
use Illuminate\Support\Collection;

class ServiceTemplatePolicy
{
    use ChecksGlobalPermissions;

    /**
     * Determine whether the user can view any service templates.
     *
     * @param  User  $user
     */
    public function viewAny(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'view')
            || $this->hasGlobalPermission($user, 'create')
            || $this->hasGlobalPermission($user, 'update')
            || $this->hasGlobalPermission($user, 'delete');
    }

    /**
     * Determine whether the user can view the service template.
     *
     * @param  User  $user
     * @param  ServiceTemplate  $template
     */
    public function view(User $user, ServiceTemplate $template): bool
    {
        return $this->hasGlobalPermission($user, 'view');
    }

    /**
     * Determine whether the user can create service templates.
     *
     * @param  User  $user
     */
    public function create(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'create');
    }

    /**
     * Determine whether the user can update the service template.
     *
     * @param  User  $user
     * @param  ServiceTemplate  $template
     */
    public function update(User $user, ServiceTemplate $template): bool
    {
        return $this->hasGlobalPermission($user, 'update');
    }

    /**
     * Determine whether the user can delete the service template.
     *
     * @param  User  $user
     * @param  ServiceTemplate  $template
     */
    public function delete(User $user, ServiceTemplate $template): bool
    {
        return $this->hasGlobalPermission($user, 'delete');
    }

    public function attachDevices(User $user, ServiceTemplate $template, Device $device): bool
    {
        return $this->hasGlobalPermission($user, 'update');
    }

    public function syncDevices(User $user, ServiceTemplate $template, Collection $devices): bool
    {
        return $this->hasGlobalPermission($user, 'update');
    }

    public function detachDevices(User $user, ServiceTemplate $template, Device $device): bool
    {
        return $this->hasGlobalPermission($user, 'update');
    }

    public function attachDeviceGroups(User $user, ServiceTemplate $template, DeviceGroup $group): bool
    {
        return $this->hasGlobalPermission($user, 'update');
    }

    public function syncDeviceGroups(User $user, ServiceTemplate $template, Collection $groups): bool
    {
        return $this->hasGlobalPermission($user, 'update');
    }

    public function detachDeviceGroups(User $user, ServiceTemplate $template, DeviceGroup $group): bool
    {
        return $this->hasGlobalPermission($user, 'update');
    }
}
