<?php

namespace App\Policies;

use App\Facades\Permissions;
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
        return $this->hasGlobalPermission($user, 'view', true)
            || $this->hasGlobalPermission($user, 'viewAll')
            || $this->hasGlobalPermission($user, 'create')
            || $this->hasGlobalPermission($user, 'update')
            || $this->hasGlobalPermission($user, 'delete');
    }

    public function manage(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'create')
            || $this->hasGlobalPermission($user, 'update')
            || $this->hasGlobalPermission($user, 'delete');
    }

    /**
     * Determine whether the user can view the port group.
     */
    public function view(User $user, PortGroup $portGroup): bool
    {
        if ($this->hasGlobalPermission($user, 'viewAll')) {
            return true;
        }

        return $this->hasGlobalPermission($user, 'view', true)
            && Permissions::canAccessPortGroup($portGroup, $user);
    }

    public function create(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'create');
    }

    /**
     * Determine whether the user can update the port group.
     */
    public function update(User $user, PortGroup $portGroup): bool
    {
        return $this->hasGlobalPermission($user, 'update')
            && Permissions::canAccessPortGroup($portGroup, $user);
    }

    /**
     * Determine whether the user can delete the port group.
     */
    public function delete(User $user, PortGroup $portGroup): bool
    {
        return $this->hasGlobalPermission($user, 'delete')
            && Permissions::canAccessPortGroup($portGroup, $user);
    }
}
