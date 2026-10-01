<?php

namespace App\Policies;

use App\Facades\Permissions;
use App\Models\DeviceOutage;
use App\Models\User;

class DeviceOutagePolicy
{
    use ChecksGlobalPermissions;

    public function __construct()
    {
        $this->globalPrefix = 'outage';
    }

    public function viewAny(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'view', true)
            || $this->hasGlobalPermission($user, 'viewAll');
    }

    public function view(User $user, DeviceOutage $deviceOutage): bool
    {
        if ($this->hasGlobalPermission($user, 'viewAll')) {
            return true;
        }

        return $this->hasGlobalPermission($user, 'view', true)
            && Permissions::canAccessDevice($deviceOutage->device_id, $user);
    }
}
