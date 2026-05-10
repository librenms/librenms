<?php

namespace App\Policies;

use App\Facades\Permissions;
use App\Models\UcdDiskio;
use App\Models\User;

class UcdDiskioPolicy
{
    use ChecksGlobalPermissions;

    public function __construct()
    {
        $this->globalPrefix = 'diskio';
    }

    public function viewAny(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'view', true);
    }

    public function view(User $user, UcdDiskio $ucdDiskio): bool
    {
        return $this->hasGlobalPermission($user, 'view', true)
            && Permissions::canAccessDevice($ucdDiskio->device_id, $user);
    }
}
