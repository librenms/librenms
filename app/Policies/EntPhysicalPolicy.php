<?php

namespace App\Policies;

use App\Facades\Permissions;
use App\Models\EntPhysical;
use App\Models\User;

class EntPhysicalPolicy
{
    use ChecksGlobalPermissions;

    public function __construct()
    {
        $this->globalPrefix = 'inventory';
    }

    public function viewAny(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'view', true)
            || $this->hasGlobalPermission($user, 'viewAll')
            || $this->hasGlobalPermission($user, 'update')
            || $this->hasGlobalPermission($user, 'delete');
    }

    public function view(User $user, EntPhysical $entPhysical): bool
    {
        if ($this->hasGlobalPermission($user, 'viewAll')) {
            return true;
        }

        return $this->hasGlobalPermission($user, 'view', true)
            && Permissions::canAccessDevice($entPhysical->device_id, $user);
    }

    public function purge(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'purge');
    }
}
