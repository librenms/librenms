<?php

namespace App\Policies;

use App\Facades\Permissions;
use App\Models\Processor;
use App\Models\User;

class ProcessorPolicy
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
    public function view(User $user, Processor $processor): bool
    {
        if ($this->hasGlobalPermission($user, 'viewAll')) {
            return true;
        }

        return $this->hasGlobalPermission($user, 'view')
            && Permissions::canAccessDevice($processor->device_id, $user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'update');
    }
}
