<?php

namespace App\Policies;

use App\Models\PollerCluster;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PollerClusterPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any poller clusters.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->hasGlobalAdmin();
    }

    /**
     * Determine whether the user can view the poller cluster.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\PollerCluster  $pollerCluster
     * @return mixed
     */
    public function view(User $user, PollerCluster $pollerCluster)
    {
        //
    }

    /**
     * Determine whether the user can create poller clusters.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the poller cluster.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\PollerCluster  $pollerCluster
     * @return mixed
     */
    public function update(User $user, PollerCluster $pollerCluster)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the poller cluster.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\PollerCluster  $pollerCluster
     * @return mixed
     */
    public function delete(User $user, PollerCluster $pollerCluster)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the poller cluster.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\PollerCluster  $pollerCluster
     * @return mixed
     */
    public function restore(User $user, PollerCluster $pollerCluster)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the poller cluster.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\PollerCluster  $pollerCluster
     * @return mixed
     */
    public function forceDelete(User $user, PollerCluster $pollerCluster)
    {
        //
    }

    /**
     * Determine whether the user can manage the poller cluster.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function manage(User $user)
    {
        return $user->isAdmin();
    }
}
