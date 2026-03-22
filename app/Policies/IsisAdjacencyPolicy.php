<?php

namespace App\Policies;

use App\Facades\Permissions;
use App\Models\IsisAdjacency;
use App\Models\User;

class IsisAdjacencyPolicy
{
    use ChecksGlobalPermissions;

    public function __construct()
    {
        $this->globalPrefix = 'routing';
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, IsisAdjacency|array $isisAdjacency): bool
    {
        if ($this->hasGlobalPermission($user, 'viewAny')) {
            return true;
        }

        $device_id = $isisAdjacency->device_id ?? $isisAdjacency['device_id'];

        return $this->hasGlobalPermission($user, 'view') &&
            Permissions::canAccessDevice($device_id, $user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, IsisAdjacency|array $isisAdjacency): bool
    {
        if ($this->hasGlobalPermission($user, 'update') && $this->hasGlobalPermission($user, 'viewAny')) {
            return true;
        }

        $device_id = $isisAdjacency->device_id ?? $isisAdjacency['device_id'];

        return $this->hasGlobalPermission($user, 'update') &&
            Permissions::canAccessDevice($device_id, $user);
    }
}
