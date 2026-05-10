<?php

namespace App\Policies;

use App\Facades\Permissions;
use App\Models\PortsNac;
use App\Models\User;

class PortsNacPolicy
{
    use ChecksGlobalPermissions;

    public function __construct() {
        $this->globalPrefix = 'port';
    }

    public function viewAny(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'view', true)
            || $this->hasGlobalPermission($user, 'viewAll')
            || $this->hasGlobalPermission($user, 'update')
            || $this->hasGlobalPermission($user, 'delete');
    }

    public function view(User $user, PortsNac $portsNac): bool
    {
        if ($this->hasGlobalPermission($user, 'viewAll')) {
            return true;
        }

        return $this->hasGlobalPermission($user, 'view', true)
            && (Permissions::canAccessDevice($portsNac->device_id, $user)
                || Permissions::canAccessPort($portsNac->port_id, $user));
    }

    public function update(User $user, PortsNac $portsNac): bool
    {
        return $this->hasGlobalPermission($user, 'update')
            && (Permissions::canAccessDevice($portsNac->device_id, $user)
                || Permissions::canAccessPort($portsNac->port_id, $user));
    }

    public function delete(User $user, PortsNac $portsNac): bool
    {
        return $this->hasGlobalPermission($user, 'delete')
            && (Permissions::canAccessDevice($portsNac->device_id, $user)
                || Permissions::canAccessPort($portsNac->port_id, $user));
    }
}
