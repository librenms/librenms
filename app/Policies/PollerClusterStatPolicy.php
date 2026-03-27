<?php

namespace App\Policies;

use App\Models\User;

class PollerClusterStatPolicy
{
    use ChecksGlobalPermissions;

    public function __construct()
    {
        $this->globalPrefix = 'poller';
    }

    public function viewAny(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'viewAny');
    }

    public function view(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'view');
    }
}
