<?php

namespace App\Policies;

use App\Models\AlertTransport;
use App\Models\AlertTransportGroup;
use App\Models\User;
use Illuminate\Support\Collection;

class AlertTransportGroupPolicy
{
    use ChecksGlobalPermissions;

    public function __construct()
    {
        $this->globalPrefix = 'alert';
    }

    public function viewAny(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'view')
            || $this->hasGlobalPermission($user, 'viewAll')
            || $this->hasGlobalPermission($user, 'create')
            || $this->hasGlobalPermission($user, 'update')
            || $this->hasGlobalPermission($user, 'delete');
    }

    public function viewAll(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'viewAll');
    }

    public function view(User $user, AlertTransportGroup $group): bool
    {
        if ($this->hasGlobalPermission($user, 'viewAll')) {
            return true;
        }

        return $this->hasGlobalPermission($user, 'view');
    }

    public function create(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'create');
    }

    public function update(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'update');
    }

    public function delete(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'delete');
    }

    public function attachTransports(User $user, AlertTransportGroup $group, AlertTransport $transport): bool
    {
        return $this->update($user);
    }

    public function syncTransports(User $user, AlertTransportGroup $group, Collection $transports): bool
    {
        return $this->update($user);
    }

    public function detachTransports(User $user, AlertTransportGroup $group, AlertTransport $transport): bool
    {
        return $this->update($user);
    }
}
