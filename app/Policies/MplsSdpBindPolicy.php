<?php

namespace App\Policies;

use App\Models\User;

class MplsSdpBindPolicy
{
    use ChecksGlobalPermissions;

    public function __construct()
    {
        $this->globalPrefix = 'routing';
    }

    public function viewAny(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'view')
            || $this->hasGlobalPermission($user, 'viewAll')
            || $this->hasGlobalPermission($user, 'update');
    }

    public function view(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'view');
    }

    public function update(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'update');
    }
}
