<?php

namespace App\Policies;

use App\Models\Poller;
use App\Models\User;

class PollerPolicy
{
    use ChecksGlobalPermissions;

    /**
     * Determine whether the user can view any pollers.
     */
    public function viewAny(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'view')
            || $this->hasGlobalPermission($user, 'update')
            || $this->hasGlobalPermission($user, 'delete');
    }

    /**
     * Determine whether the user can view the poller.
     */
    public function view(User $user, Poller $poller): bool
    {
        return $this->hasGlobalPermission($user, 'view');
    }

    /**
     * Determine whether the user can update the poller.
     */
    public function update(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'update');
    }

    /**
     * Determine whether the user can delete the poller.
     */
    public function delete(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'delete');
    }
}
