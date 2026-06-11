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
        $this->globalPrefix = 'alert-operation';
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
}
