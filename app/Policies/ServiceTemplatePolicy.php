<?php

namespace App\Policies;

use App\Models\User;

class ServiceTemplatePolicy
{
    use ChecksGlobalPermissions;

    /**
     * Determine whether the user can view any service templates.
     *
     * @param  User  $user
     */
    public function viewAny(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'viewAny');
    }

    /**
     * Determine whether the user can view the service template.
     *
     * @param  User  $user
     * @param  ServiceTemplate  $template
     */
    public function view(User $user): bool
    {
        if ($this->hasGlobalPermission($user, 'viewAny')) {
            return true;
        }

        return $this->hasGlobalPermission($user, 'view');
    }

    /**
     * Determine whether the user can create service templates.
     *
     * @param  User  $user
     */
    public function create(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'create');
    }

    /**
     * Determine whether the user can update the service template.
     *
     * @param  User  $user
     * @param  ServiceTemplate  $template
     */
    public function update(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'update');
    }

    /**
     * Determine whether the user can delete the service template.
     *
     * @param  User  $user
     * @param  ServiceTemplate  $template
     */
    public function delete(User $user, int $serviceTemplateId): bool
    {
        return $this->hasGlobalPermission($user, 'delete');
    }
}
