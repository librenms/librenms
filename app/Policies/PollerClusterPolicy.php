<?php

namespace App\Policies;

use App\Models\PollerCluster;
use App\Models\User;

class PollerClusterPolicy
{
    use ChecksGlobalPermissions;

    /**
     * Determine whether the user can view any poller clusters.
     *
     * @param  User  $user
     */
    public function viewAny(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'viewAny');
    }

    /**
     * Determine whether the user can view the poller cluster.
     *
     * @param  User  $user
     * @param  PollerCluster  $pollerCluster
     */
    public function view(User $user, PollerCluster $pollerCluster): bool
    {
        return $this->hasGlobalPermission($user, 'view');
    }

    /**
     * Determine whether the user can create poller clusters.
     *
     * @param  User  $user
     */
    public function create(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'create');
    }

    /**
     * Determine whether the user can update the poller cluster.
     *
     * @param  User  $user
     * @param  PollerCluster  $pollerCluster
     */
    public function update(User $user, PollerCluster $pollerCluster): bool
    {
        return $this->hasGlobalPermission($user, 'update');
    }

    /**
     * Determine whether the user can delete the poller cluster.
     *
     * @param  User  $user
     * @param  PollerCluster  $pollerCluster
     */
    public function delete(User $user, PollerCluster $pollerCluster): bool
    {
        return $this->hasGlobalPermission($user, 'delete');
    }

    /**
     * Determine whether the user can restore the poller cluster.
     *
     * @param  User  $user
     * @param  PollerCluster  $pollerCluster
     */
    public function restore(User $user, PollerCluster $pollerCluster): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can manage the poller cluster.
     *
     * @param  User  $user
     */
    public function manage(User $user): bool
    {
        return $user->isAdmin();
    }
}
