<?php

namespace App\Policies;

use App\Models\ServiceTemplate;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ServiceTemplatePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any service templates.
     *
     * @param  \App\Models\User  $user
     */
    public function viewAny(User $user): bool
    {
        return $user->hasGlobalRead();
    }

    /**
     * Determine whether the user can view the service template.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ServiceTemplate  $template
     */
    public function view(User $user, ServiceTemplate $template): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can create service templates.
     *
     * @param  \App\Models\User  $user
     */
    public function create(User $user): bool
    {
        return $user->hasGlobalAdmin();
    }

    /**
     * Determine whether the user can update the service template.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ServiceTemplate  $template
     */
    public function update(User $user, ServiceTemplate $template): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the service template.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ServiceTemplate  $template
     */
    public function delete(User $user, ServiceTemplate $template): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the service template.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ServiceTemplate  $template
     */
    public function restore(User $user, ServiceTemplate $template): bool
    {
        return $user->hasGlobalAdmin();
    }

    /**
     * Determine whether the user can permanently delete the service template.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ServiceTemplate  $template
     */
    public function forceDelete(User $user, ServiceTemplate $template): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view the stored configuration of the service template
     * from Oxidized or Rancid
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ServiceTemplate  $template
     */
    public function showConfig(User $user, ServiceTemplate $template): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update service template notes.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ServiceTemplate  $template
     */
    public function updateNotes(User $user, ServiceTemplate $template): bool
    {
        return $user->isAdmin();
    }
}
