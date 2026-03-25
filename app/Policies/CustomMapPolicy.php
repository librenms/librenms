<?php

namespace App\Policies;

use App\Facades\Permissions;
use App\Models\CustomMap;
use App\Models\User;

class CustomMapPolicy
{
    use ChecksGlobalPermissions;

    /**
     * Determine whether the user can view any models.
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
     * Determine whether the user can view the model.
     */
    public function view(User $user, CustomMap $customMap): bool
    {
        if ($this->hasGlobalPermission($user, 'viewAll')) {
            return true;
        }

        if ($this->hasGlobalPermission($user, 'view')) {
            $device_ids = $customMap->nodes()->whereNotNull('device_id')->pluck('device_id');

            // Restricted users can only view maps that have at least one device
            if (count($device_ids) === 0) {
                return false;
            }

            // Deny access if we don't have permission on any device
            foreach ($device_ids as $device_id) {
                if (! Permissions::canAccessDevice($device_id, $user)) {
                    return false;
                }
            }

            return true;
        }

        return false;
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
    public function update(User $user, CustomMap $customMap): bool
    {
        return $this->hasGlobalPermission($user, 'update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CustomMap $customMap): bool
    {
        return $this->hasGlobalPermission($user, 'delete');
    }
}
