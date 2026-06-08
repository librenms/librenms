<?php

namespace App\Policies;

use App\Models\AlertOperationSegment;
use App\Models\User;

/**
 * Segments are managed through their parent {@see \App\Models\AlertOperation}.
 * Authorization mirrors the parent: the v1 API exposes segments as a read-only
 * sub-resource so {@see \App\Restify\AlertOperationSegmentRepository} only needs
 * view/viewAny here. Writes happen via the operation, gated by AlertOperationPolicy.
 */
class AlertOperationSegmentPolicy
{
    use ChecksGlobalPermissions;

    public function __construct()
    {
        $this->globalPrefix = 'alert-rule';
    }

    public function viewAny(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'view')
            || $this->hasGlobalPermission($user, 'viewAll')
            || $this->hasGlobalPermission($user, 'create')
            || $this->hasGlobalPermission($user, 'update')
            || $this->hasGlobalPermission($user, 'delete');
    }

    public function viewAll(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'viewAll');
    }

    public function view(User $user, AlertOperationSegment $segment): bool
    {
        if ($this->hasGlobalPermission($user, 'viewAll')) {
            return true;
        }

        return $this->hasGlobalPermission($user, 'view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'create');
    }

    /**
     * Determine whether the user can update models.
     */
    public function update(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'update');
    }

    /**
     * Determine whether the user can delete models.
     */
    public function delete(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'delete');
    }
}
