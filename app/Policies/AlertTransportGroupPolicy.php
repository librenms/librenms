<?php

namespace App\Policies;

use App\Models\AlertTransportGroup;
use App\Models\User;

class AlertTransportGroupPolicy
{
    use ChecksGlobalPermissions;

    public function __construct()
    {
        $this->globalPrefix = 'alert-transport';
    }

    public function viewAny(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'view')
            || $this->hasGlobalPermission($user, 'create')
            || $this->hasGlobalPermission($user, 'update')
            || $this->hasGlobalPermission($user, 'delete');
    }

    public function view(User $user, AlertTransportGroup $group): bool
    {
        return $this->hasGlobalPermission($user, 'view');
    }

    public function create(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'create');
    }

    public function update(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'update');
    }

    public function delete(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'delete');
    }
}
