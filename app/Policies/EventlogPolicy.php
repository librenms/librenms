<?php

namespace App\Policies;

use App\Facades\Permissions;
use App\Models\Eventlog;
use App\Models\User;

class EventlogPolicy
{
    use ChecksGlobalPermissions;

    public function viewAny(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'view', true)
            || $this->hasGlobalPermission($user, 'viewAll');
    }

    public function view(User $user, Eventlog $eventLog): bool
    {
        if ($this->hasGlobalPermission($user, 'viewAll')) {
            return true;
        }

        return $this->hasGlobalPermission($user, 'view', true)
            && Permissions::canAccessDevice($eventLog->device_id, $user);
    }
}
