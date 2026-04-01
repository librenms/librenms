<?php

namespace App\Policies;

use App\Models\User;

class Ospfv3AreaPolicy
{
    use ChecksGlobalPermissions;

    public function __construct()
    {
        $this->globalPrefix = 'routing';
    }

    public function viewAny(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'viewAny');
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
