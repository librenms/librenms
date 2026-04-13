<?php

namespace App\Policies;

use App\Models\User;

class SyslogPolicy
{
    use ChecksGlobalPermissions;

    public function viewAny(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'delete');
    }

    public function view(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'delete');
    }
}
