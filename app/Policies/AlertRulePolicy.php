<?php

namespace App\Policies;

use App\Facades\Permissions;
use App\Models\AlertRule;
use App\Models\User;

class AlertRulePolicy
{
    use ChecksGlobalPermissions;

    /**
     * Determine whether the user can view any models.
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
     * Determine whether the user can view the model.
     */
    public function view(User $user, AlertRule $alertRule): bool
    {
        if ($this->hasGlobalPermission($user, 'viewAll')) {
            return true;
        }

        if (! $this->hasGlobalPermission($user, 'view', true)) {
            return false;
        }

        // FIXME probably a less brain-dead way to do this
        foreach ($alertRule->devices as $device) {
            if (Permissions::canAccessDevice($device, $user)) {
                return true;
            }
        }

        foreach ($alertRule->groups as $group) {
            if (Permissions::canAccessDeviceGroup($group, $user)) {
                return true;
            }
        }

        foreach ($alertRule->locations as $location) {
            foreach($location->devices as $device) {
                if (Permissions::canAccessDevice($device, $user)) {
                    return true;
                }
            }
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
    public function update(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'delete');
    }
}
