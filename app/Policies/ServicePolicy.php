<?php

namespace App\Policies;

use App\Facades\LibrenmsConfig;
use App\Facades\Permissions;
use App\Models\Service;
use App\Models\User;

class ServicePolicy
{
    use ChecksGlobalPermissions;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if (! LibrenmsConfig::get('show_services')) {
            return false;
        }

        return $this->hasGlobalPermission($user, 'view', true)
            || $this->hasGlobalPermission($user, 'viewAll')
            || $this->hasGlobalPermission($user, 'create')
            || $this->hasGlobalPermission($user, 'update')
            || $this->hasGlobalPermission($user, 'delete');
    }

    public function viewAll(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'viewAll');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Service $service): bool
    {
        if ($this->hasGlobalPermission($user, 'viewAll')) {
            return true;
        }

        return $this->hasGlobalPermission($user, 'view', true)
            && Permissions::canAccessDevice($service->device_id, $user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Service $service): bool
    {
        return $this->hasGlobalPermission($user, 'update')
            && Permissions::canAccessDevice($service->device_id, $user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Service $service): bool
    {
        return $this->hasGlobalPermission($user, 'delete')
            && Permissions::canAccessDevice($service->device_id, $user);
    }
}
