<?php

namespace App\Policies;

use App\Facades\Permissions;
use App\Models\Link;
use App\Models\User;

class LinkPolicy
{
    use ChecksGlobalPermissions;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'view', true)
            || $this->hasGlobalPermission($user, 'viewAll');
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
    public function view(User $user, Link $link): bool
    {
        if ($this->hasGlobalPermission($user, 'viewAll')) {
            return true;
        }

        return $this->hasGlobalPermission($user, 'view', true)
            && (Permissions::canAccessDevice($link->local_device_id, $user) || Permissions::canAccessPort($link->local_port_id))
            && ($link->remote_device_id == 0 || Permissions::canAccessDevice($link->remote_device_id, $user) || Permissions::canAccessPort($link->remote_port_id));
    }
}
