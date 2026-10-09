<?php

namespace App\Policies;

use App\Models\User;

/**
 * Shared policy for port-scoped, read-only resources.
 *
 * Authorization is governed entirely by the global `port` permission set;
 * row-level port/device access is enforced by each model's query scope, not here.
 * Concrete per-model policies extend this so Laravel can resolve them by the
 * `<Model>Policy` naming convention.
 */
abstract class PortRelatedPolicy
{
    use ChecksGlobalPermissions;

    public function __construct()
    {
        $this->globalPrefix = 'port';
    }

    public function viewAny(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'view')
            || $this->hasGlobalPermission($user, 'viewAll')
            || $this->hasGlobalPermission($user, 'update')
            || $this->hasGlobalPermission($user, 'delete');
    }

    public function view(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'view');
    }
}
