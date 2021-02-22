<?php

namespace App\Policies;

use App\Models\Service;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ServicePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any services.
     *
     * @param \App\Models\User $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->hasGlobalRead();
    }

    /**
     * Determine whether the user can view the service.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Service $service
     * @return mixed
     */
    public function view(User $user, Service $service)
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can create services.
     *
     * @param \App\Models\User $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->hasGlobalAdmin();
    }

    /**
     * Determine whether the user can update the service.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Service $service
     * @return mixed
     */
    public function update(User $user, Service $service)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the service.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Service $service
     * @return mixed
     */
    public function delete(User $user, Service $service)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the service.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Service $service
     * @return mixed
     */
    public function restore(User $user, Service $service)
    {
        return $user->hasGlobalAdmin();
    }

    /**
     * Determine whether the user can permanently delete the service.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Service $service
     * @return mixed
     */
    public function forceDelete(User $user, Service $service)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view the stored configuration of the service
     * from Oxidized or Rancid
     *
     * @param \App\Models\User $user
     * @param \App\Models\Service $service
     * @return mixed
     */
    public function showConfig(User $user, Service $service)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update service notes.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Service $service
     * @return mixed
     */
    public function updateNotes(User $user, Service $service)
    {
        return $user->isAdmin();
    }
}
