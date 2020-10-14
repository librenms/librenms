<?php

namespace App\Policies;

use App\Models\ServiceTemplate;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ServiceTemplatePolicy
{
    use HandlesAuthorization;

    public function before($user, $ability)
    {
        if ($user->isAdmin()) {
            return true;
        }
    }

    /**
     * Determine whether the user can manage services templates.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function manage(User $user)
    {
        return false;
    }

    /**
     * Determine whether the user can view the services template.
     *
     * @param \App\Models\User $user
     * @param \App\Models\ServiceTemplate $serviceTemplate
     * @return mixed
     */
    public function view(User $user, ServiceTemplate $serviceTemplate)
    {
        return false;
    }

    /**
     * Determine whether the user can view any services template.
     *
     * @param  \App\User $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return false;
    }

    /**
     * Determine whether the user can create services templates.
     *
     * @param \App\Models\User $user
     * @return mixed
     */
    public function create(User $user)
    {
        return false;
    }

    /**
     * Determine whether the user can update the services template.
     *
     * @param \App\Models\User $user
     * @param \App\Models\ServiceTemplate $serviceTemplate
     * @return mixed
     */
    public function update(User $user, ServiceTemplate $serviceTemplate)
    {
        return false;
    }

    /**
     * Determine whether the user can delete the services template.
     *
     * @param \App\Models\User $user
     * @param \App\Models\ServiceTemplate $serviceTemplate
     * @return mixed
     */
    public function delete(User $user, ServiceTemplate $serviceTemplate)
    {
        return false;
    }

    /**
     * Determine whether the user can restore the services template.
     *
     * @param \App\Models\User $user
     * @param \App\Models\ServiceTemplate $serviceTemplate
     * @return mixed
     */
    public function restore(User $user, ServiceTemplate $serviceTemplate)
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the services template.
     *
     * @param \App\Models\User $user
     * @param \App\Models\ServiceTemplate $serviceTemplate
     * @return mixed
     */
    public function forceDelete(User $user, ServiceTemplate $serviceTemplate)
    {
        return false;
    }
}
