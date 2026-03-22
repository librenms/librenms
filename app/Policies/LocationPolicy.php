<?php

namespace App\Policies;

use App\Models\Location;
use App\Models\User;

class LocationPolicy
{
    use ChecksGlobalPermissions;

    /**
     * Determine whether the user can view any locations.
     */
    public function viewAny(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'viewAny');
    }

    /**
     * Determine whether the user can view the location.
     */
    public function view(User $user, Location|array $location): bool
    {
        if ($this->hasGlobalPermission($user, 'viewAny')) {
            return true;
        }

        $location_id = $location->id ?? $location['id'];

        return $this->hasGlobalPermission($user, 'view')
            || Location::hasAccess($user)->where('id', $location_id)->exists(); // FIXME not a db query
    }

    /**
     * Determine whether the user can create locations.
     */
    public function create(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'create');
    }

    /**
     * Determine whether the user can update the location.
     */
    public function update(User $user, Location|array $location): bool
    {
        if ($this->hasGlobalPermission($user, 'update') && $this->hasGlobalPermission($user, 'viewAny')) {
            return true;
        }

        $location_id = $location->id ?? $location['id'];

        return $this->hasGlobalPermission($user, 'update') &&
            Location::hasAccess($user)->where('id', $location_id)->exists(); // FIXME not a db query
    }

    /**
     * Determine whether the user can delete the location.
     */
    public function delete(User $user, Location|array $location): bool
    {
        if ($this->hasGlobalPermission($user, 'delete') && $this->hasGlobalPermission($user, 'viewAny')) {
            return true;
        }

        $location_id = $location->id ?? $location['id'];

        return $this->hasGlobalPermission($user, 'delete') &&
            Location::hasAccess($user)->where('id', $location_id)->exists(); // FIXME not a db query
    }
}
