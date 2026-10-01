<?php

namespace App\Policies;

use App\Facades\Permissions;
use App\Models\AlertLog;
use App\Models\User;

class AlertLogPolicy
{
    use ChecksGlobalPermissions;

    public function viewAny(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'viewAll')
            || $this->hasGlobalPermission($user, 'view', true);
    }

    public function view(User $user, AlertLog $alertLog): bool
    {
        if ($this->hasGlobalPermission($user, 'viewAll')) {
            return true;
        }

        return $this->hasGlobalPermission($user, 'view', true)
            && Permissions::canAccessDevice($alertLog->device_id, $user);
    }
}
