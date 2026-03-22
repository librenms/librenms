<?php

namespace App\Policies;

use App\Facades\Permissions;
use App\Models\Application;
use App\Models\User;

class ApplicationPolicy
{
    use ChecksGlobalPermissions;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Application|array $application): bool
    {
        if ($this->hasGlobalPermission($user, 'viewAny')) {
            return true;
        }

        $device_id = $application->device_id ?? $application['device_id'];

        return $this->hasGlobalPermission($user, 'view') &&
            Permissions::canAccessDevice($device_id, $user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Application|array $application): bool
    {
        if ($this->hasGlobalPermission($user, 'update') && $this->hasGlobalPermission($user, 'viewAny')) {
            return true;
        }

        $device_id = $application->device_id ?? $application['device_id'];

        return $this->hasGlobalPermission($user, 'update') &&
            Permissions::canAccessDevice($device_id, $user);
    }
}
