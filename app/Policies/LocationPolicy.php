<?php

namespace App\Policies;

use App\Models\Location;
use App\Models\User;

class LocationPolicy
{
    use ChecksGlobalPermissions;

    /**
     * Determine whether the user can view any locations.
     *
     * @param  User  $user
     */
    public function viewAny(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'viewAny');
    }

    /**
     * Determine whether the user can view the location.
     *
     * @param  User  $user
     * @param  Location  $location
     */
    public function view(User $user, Location $location): bool
    {
        return $this->hasGlobalPermission($user, 'view')
            || Location::hasAccess($user)->where('id', $location->id)->exists(); // FIXME not a db query
    }

    /**
     * Determine whether the user can create locations.
     *
     * @param  User  $user
     */
    public function create(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'create');
    }

    /**
     * Determine whether the user can update the location.
     *
     * @param  User  $user
     * @param  Location  $location
     */
    public function update(User $user, Location $location): bool
    {
        return $this->hasGlobalPermission($user, 'update');
    }

    /**
     * Determine whether the user can delete the location.
     *
     * @param  User  $user
     * @param  Location  $location
     */
    public function delete(User $user, Location $location): bool
    {
        return $this->hasGlobalPermission($user, 'delete');
    }
}
