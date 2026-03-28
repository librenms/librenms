<?php

namespace App\Policies;

use App\Facades\LibrenmsConfig;
use App\Models\PortGroup;
use App\Models\User;

class PortGroupPolicy
{
    use ChecksGlobalPermissions;

    /**
     * Determine whether the user can view any port group.
     */
    public function viewAny(User $user): bool
    {
        if (! LibrenmsConfig::get('distributed_poller')) {
            return false;
        }

        return $this->hasGlobalPermission($user, 'viewAll')
            || $this->hasGlobalPermission($user, 'create')
            || $this->hasGlobalPermission($user, 'update')
            || $this->hasGlobalPermission($user, 'delete');
    }

    /**
     * Determine whether the user can view the port group.
     */
    public function view(User $user, PortGroup $portGroup): bool
    {
        return $this->hasGlobalPermission($user, 'viewAll');
    }

    /**
     * Determine whether the user can update the port group.
     */
    public function update(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'update');
    }

    /**
     * Determine whether the user can delete the port group.
     */
    public function delete(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'delete');
    }
}
