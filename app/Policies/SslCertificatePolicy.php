<?php

namespace App\Policies;

use App\Models\User;

class SslCertificatePolicy
{
    use ChecksGlobalPermissions;

    /**
     * Determine whether the user can view any SSL certificates.
     */
    public function viewAny(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'view')
            || $this->hasGlobalPermission($user, 'viewAll')
            || $this->hasGlobalPermission($user, 'create')
            || $this->hasGlobalPermission($user, 'delete');
    }

    /**
     * Determine whether the user can view all SSL certificates.
     */
    public function viewAll(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'viewAll');
    }

    /**
     * Determine whether the user can view the SSL certificate.
     */
    public function view(User $user): bool
    {
        if ($this->hasGlobalPermission($user, 'viewAll')) {
            return true;
        }

        return $this->hasGlobalPermission($user, 'view');
    }

    /**
     * Determine whether the user can create SSL certificates.
     */
    public function create(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'create');
    }

    /**
     * Determine whether the user can delete the SSL certificate.
     */
    public function delete(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'delete');
    }
}
