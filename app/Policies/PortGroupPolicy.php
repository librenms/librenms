<?php

namespace App\Policies;

use App\Models\User;

class PortGroupPolicy
{
    use ChecksGlobalPermissions;

    /**
     * Determine whether the user can view any port group.
     */
    public function viewAny(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'viewAny');
    }

    /**
     * Determine whether the user can view the port group.
     */
    public function view(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'view');
    }

    /**
     * Determine whether the user can update the port group.
     */
    public function update(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'update');
    }

    /**
     * Determine whether the user can delete the port group.
     */
    public function delete(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'delete');
    }
}
