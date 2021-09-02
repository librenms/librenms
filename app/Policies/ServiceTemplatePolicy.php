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
     * @param \App\Models\User $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->hasGlobalRead();
    }

    /**
     * Determine whether the user can view the service template.
     *
     * @param \App\Models\User $user
     * @param \App\Models\ServiceTemplate $template
     * @return mixed
     */
    public function view(User $user, ServiceTemplate $template)
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can create service templates.
     *
     * @param \App\Models\User $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->hasGlobalAdmin();
    }

    /**
     * Determine whether the user can update the service template.
     *
     * @param \App\Models\User $user
     * @param \App\Models\ServiceTemplate $template
     * @return mixed
     */
    public function update(User $user, ServiceTemplate $template)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the service template.
     *
     * @param \App\Models\User $user
     * @param \App\Models\ServiceTemplate $template
     * @return mixed
     */
    public function delete(User $user, ServiceTemplate $template)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the service template.
     *
     * @param \App\Models\User $user
     * @param \App\Models\ServiceTemplate $template
     * @return mixed
     */
    public function restore(User $user, ServiceTemplate $template)
    {
        return $user->hasGlobalAdmin();
    }

    /**
     * Determine whether the user can permanently delete the service template.
     *
     * @param \App\Models\User $user
     * @param \App\Models\ServiceTemplate $template
     * @return mixed
     */
    public function forceDelete(User $user, ServiceTemplate $template)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view the stored configuration of the service template
     * from Oxidized or Rancid
     *
     * @param \App\Models\User $user
     * @param \App\Models\ServiceTemplate $template
     * @return mixed
     */
    public function showConfig(User $user, ServiceTemplate $template)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update service template notes.
     *
     * @param \App\Models\User $user
     * @param \App\Models\ServiceTemplate $template
     * @return mixed
     */
    public function updateNotes(User $user, ServiceTemplate $template)
    {
        return $user->isAdmin();
    }
}
