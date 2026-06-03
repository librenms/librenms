<?php

namespace App\Policies;

use App\Models\User;

/**
 * Shared policy for routing-scoped resources (MPLS, OSPF, VRF, ...).
 *
 * Authorization is governed entirely by the global `routing` permission set;
 * row-level device access is enforced by each model's query scope, not here.
 * Concrete per-model policies extend this so Laravel can resolve them by the
 * `<Model>Policy` naming convention.
 */
abstract class RoutingRelatedPolicy
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
}
