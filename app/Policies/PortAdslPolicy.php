<?php

namespace App\Policies;

use App\Models\User;

class PortAdslPolicy
{
    use ChecksGlobalPermissions;

    public function viewAny(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'viewAny');
    }

    public function view(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'view');
    }
}
