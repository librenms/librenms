<?php

namespace App\Policies;

use App\Models\Bill;
use App\Models\Device;
use App\Models\DeviceGroup;
use App\Models\Port;
use App\Models\User;
use Illuminate\Support\Collection;

class UserPolicy
{
    use ChecksGlobalPermissions;

    public function viewAny(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'view')
            || $this->hasGlobalPermission($user, 'create')
            || $this->hasGlobalPermission($user, 'update')
            || $this->hasGlobalPermission($user, 'delete');
    }

    /**
     * Determine whether the user can view the user.
     */
    public function view(User $user, ?User $target = null): bool
    {
        return ($target && $user->is($target)) // allow users to view themselves
            || $this->hasGlobalPermission($user, 'view');
    }

    /**
     * Determine whether the user can create users.
     */
    public function create(User $user): bool
    {
        // if not mysql, forbid
        if (\App\Facades\LibrenmsConfig::get('auth_mechanism') != 'mysql') {
            return false;
        }

        return $this->hasGlobalPermission($user, 'create');
    }

    /**
     * Determine whether the user can update the user.
     */
    public function update(User $user, ?User $target = null): bool
    {
        return ($target && $user->is($target)) // allow users to update themselves
            || $this->hasGlobalPermission($user, 'update');
    }

    /**
     * Determine whether the user can delete the user.
     */
    public function delete(User $user, ?User $target = null): bool
    {
        // do not allow users to delete themselves
        if ($target && $user->is($target)) {
            return false;
        }

        return $this->hasGlobalPermission($user, 'delete');
    }

    public function attachDevicesOwned(User $user, User $target, Device $device): bool
    {
        return $this->update($user, $target);
    }

    public function syncDevicesOwned(User $user, User $target, Collection $devices): bool
    {
        return $this->update($user, $target);
    }

    public function detachDevicesOwned(User $user, User $target, Device $device): bool
    {
        return $this->update($user, $target);
    }

    public function attachPortsOwned(User $user, User $target, Port $port): bool
    {
        return $this->update($user, $target);
    }

    public function syncPortsOwned(User $user, User $target, Collection $ports): bool
    {
        return $this->update($user, $target);
    }

    public function detachPortsOwned(User $user, User $target, Port $port): bool
    {
        return $this->update($user, $target);
    }

    public function attachBills(User $user, User $target, Bill $bill): bool
    {
        return $this->update($user, $target);
    }

    public function syncBills(User $user, User $target, Collection $bills): bool
    {
        return $this->update($user, $target);
    }

    public function detachBills(User $user, User $target, Bill $bill): bool
    {
        return $this->update($user, $target);
    }

    public function attachDeviceGroups(User $user, User $target, DeviceGroup $group): bool
    {
        return $this->update($user, $target);
    }

    public function syncDeviceGroups(User $user, User $target, Collection $groups): bool
    {
        return $this->update($user, $target);
    }

    public function detachDeviceGroups(User $user, User $target, DeviceGroup $group): bool
    {
        return $this->update($user, $target);
    }
}
