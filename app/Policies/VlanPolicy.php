<?php

namespace App\Policies;

use App\Models\User;

class VlanPolicy
{
    use ChecksGlobalPermissions;

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
    public function view(User $user, int $vlanId): bool
    {
        if ($this->hasGlobalPermission($user, 'viewAny')) {
            return true;
        }

        return $this->hasGlobalPermission($user, 'view');
    }
}
