<?php

namespace App\Policies;

use App\Facades\Permissions;
use App\Models\Alert;
use App\Models\User;

class AlertPolicy
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
    public function view(User $user, Alert|int $alertId): bool
    {
        if ($this->hasGlobalPermission($user, 'viewAny')) {
            return true;
        }

        return $this->hasGlobalPermission($user, 'view')
            && $alertId !== null
            && Permissions::canAccessDevice($alertId, $user);
    }

    public function detail(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'detail');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, int $alertId): bool
    {
        if ($this->hasGlobalPermission($user, 'update') && $this->hasGlobalPermission($user, 'viewAny')) {
            return true;
        }

        return $this->hasGlobalPermission($user, 'update') &&
            $alertId !== null &&
            Permissions::canAccessDevice($alertId, $user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, int $alertId): bool
    {
        if ($this->hasGlobalPermission($user, 'delete') && $this->hasGlobalPermission($user, 'viewAny')) {
            return true;
        }

        return $this->hasGlobalPermission($user, 'delete') &&
            $alertId !== null &&
            Permissions::canAccessDevice($alertId, $user);
    }
}
