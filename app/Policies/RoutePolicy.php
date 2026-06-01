<?php

namespace App\Policies;

use App\Facades\Permissions;
use App\Models\Route;
use App\Models\User;

class RoutePolicy
{
    use ChecksGlobalPermissions;

    public function __construct()
    {
        $this->globalPrefix = 'routing';
    }

    public function viewAny(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'view', true)
            || $this->hasGlobalPermission($user, 'viewAll');
    }

    public function view(User $user, Route $route): bool
    {
        if ($this->hasGlobalPermission($user, 'viewAll')) {
            return true;
        }

        return $this->hasGlobalPermission($user, 'view', true)
            && Permissions::canAccessDevice($route->device_id, $user);
    }
}
