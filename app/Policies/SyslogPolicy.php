<?php

namespace App\Policies;

use App\Facades\Permissions;
use App\Models\Syslog;
use App\Models\User;

class SyslogPolicy
{
    use ChecksGlobalPermissions;

    public function viewAny(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'view', true)
            || $this->hasGlobalPermission($user, 'viewAll')
            || $this->hasGlobalPermission($user, 'delete');
    }

    public function view(User $user, Syslog $syslog): bool
    {
        if ($this->hasGlobalPermission($user, 'viewAll')) {
            return true;
        }

        return $this->hasGlobalPermission($user, 'view', true)
            && Permissions::canAccessDevice($syslog->device_id, $user);
    }

    public function delete(User $user, Syslog $syslog): bool
    {
        return $this->hasGlobalPermission($user, 'delete')
            && Permissions::canAccessDevice($syslog->device_id, $user);
    }
}
