<?php

namespace App\Policies;

use App\Facades\Permissions;
use App\Models\Port;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PortPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any ports.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->hasGlobalRead();
    }

    /**
     * Determine whether the user can view the port.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Port  $port
     * @return mixed
     */
    public function view(User $user, Port $port)
    {
        return $this->viewAny($user) || Permissions::canAccessDevice($port->device_id, $user) || Permissions::canAccessPort($port, $user);
    }

    /**
     * Determine whether the user can create ports.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return false;
    }

    /**
     * Determine whether the user can update the port.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Port  $port
     * @return mixed
     */
    public function update(User $user, Port $port)
    {
        return $user->hasGlobalAdmin();
    }

    /**
     * Determine whether the user can delete the port.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Port  $port
     * @return mixed
     */
    public function delete(User $user, Port $port)
    {
        return $user->hasGlobalAdmin();
    }

    /**
     * Determine whether the user can restore the port.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Port  $port
     * @return mixed
     */
    public function restore(User $user, Port $port)
    {
        return $user->hasGlobalAdmin();
    }

    /**
     * Determine whether the user can permanently delete the port.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Port  $port
     * @return mixed
     */
    public function forceDelete(User $user, Port $port)
    {
        return $user->hasGlobalAdmin();
    }
}
