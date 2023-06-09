<?php

namespace App\Policies;

use App\Facades\Permissions;
use App\Models\Device;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DevicePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any devices.
     *
     * @param  \App\Models\User  $user
     */
    public function viewAny(User $user): bool
    {
        return $user->hasGlobalRead();
    }

    /**
     * Determine whether the user can view the device.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Device  $device
     */
    public function view(User $user, Device $device): bool
    {
        return $this->viewAny($user) || Permissions::canAccessDevice($device, $user);
    }

    /**
     * Determine whether the user can create devices.
     *
     * @param  \App\Models\User  $user
     */
    public function create(User $user): bool
    {
        return $user->hasGlobalAdmin();
    }

    /**
     * Determine whether the user can update the device.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Device  $device
     */
    public function update(User $user, Device $device): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the device.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Device  $device
     */
    public function delete(User $user, Device $device): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the device.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Device  $device
     */
    public function restore(User $user, Device $device): bool
    {
        return $user->hasGlobalAdmin();
    }

    /**
     * Determine whether the user can permanently delete the device.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Device  $device
     */
    public function forceDelete(User $user, Device $device): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view the stored configuration of the device
     * from Oxidized or Rancid
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Device  $device
     */
    public function showConfig(User $user, Device $device): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update device notes.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Device  $device
     */
    public function updateNotes(User $user, Device $device): bool
    {
        return $user->isAdmin();
    }
}
