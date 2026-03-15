<?php

namespace App\Policies;

use App\Models\SslCertificate;
use App\Models\User;

class SslCertificatePolicy
{
    use ChecksGlobalPermissions;

    /**
     * Determine whether the user can view any SSL certificates.
     */
    public function viewAny(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'viewAny');
    }

    /**
     * Determine whether the user can view the SSL certificate.
     */
    public function view(User $user, SslCertificate $sslCertificate): bool
    {
        return $this->hasGlobalPermission($user, 'view')
            && SslCertificate::hasAccess($user)->where('id', $sslCertificate->id)->exists();
    }

    /**
     * Determine whether the user can create SSL certificates.
     */
    public function create(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'create');
    }

    /**
     * Determine whether the user can update the SSL certificate.
     */
    public function update(User $user, SslCertificate $sslCertificate): bool
    {
        return $this->hasGlobalPermission($user, 'update')
            && SslCertificate::hasAccess($user)->where('id', $sslCertificate->id)->exists();
    }

    /**
     * Determine whether the user can delete the SSL certificate.
     */
    public function delete(User $user, SslCertificate $sslCertificate): bool
    {
        return $this->hasGlobalPermission($user, 'delete')
            && SslCertificate::hasAccess($user)->where('id', $sslCertificate->id)->exists();
    }
}
