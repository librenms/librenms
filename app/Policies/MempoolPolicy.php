<?php

namespace App\Policies;

use App\Facades\Permissions;
use App\Models\Mempool;
use App\Models\User;

class MempoolPolicy
{
    use ChecksGlobalPermissions;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'view')
            || $this->hasGlobalPermission($user, 'update');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Mempool $mempool): bool
    {
        if ($this->hasGlobalPermission($user, 'viewAll')) {
            return true;
        }

        return $this->hasGlobalPermission($user, 'view')
            && Permissions::canAccessDevice($mempool->device_id, $user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'update');
    }
}
