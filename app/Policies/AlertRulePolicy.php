<?php

namespace App\Policies;

use App\Models\AlertRule;
use App\Models\AlertTemplate;
use App\Models\Device;
use App\Models\DeviceGroup;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Collection;

class AlertRulePolicy
{
    use ChecksGlobalPermissions;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'view', true)
            || $this->hasGlobalPermission($user, 'viewAll')
            || $this->hasGlobalPermission($user, 'create')
            || $this->hasGlobalPermission($user, 'update')
            || $this->hasGlobalPermission($user, 'delete');
    }

    /**
     * Determine whether the user can view all models.
     */
    public function viewAll(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'viewAll');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AlertRule $alertRule): bool
    {
        // Alert-rule visibility is governed by alert-rule permission alone, regardless of
        // device access. Device access is enforced only on the rule's related
        // devices/groups (which resolve through their own device-scoped repositories).
        return $this->hasGlobalPermission($user, 'viewAll')
            || $this->hasGlobalPermission($user, 'view', true);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'delete');
    }

    public function attachDevices(User $user, AlertRule $rule, Device $device): bool
    {
        return $this->update($user);
    }

    public function syncDevices(User $user, AlertRule $rule, Collection $devices): bool
    {
        return $this->update($user);
    }

    public function detachDevices(User $user, AlertRule $rule, Device $device): bool
    {
        return $this->update($user);
    }

    public function attachDeviceGroups(User $user, AlertRule $rule, DeviceGroup $group): bool
    {
        return $this->update($user);
    }

    public function syncDeviceGroups(User $user, AlertRule $rule, Collection $groups): bool
    {
        return $this->update($user);
    }

    public function detachDeviceGroups(User $user, AlertRule $rule, DeviceGroup $group): bool
    {
        return $this->update($user);
    }

    public function attachLocations(User $user, AlertRule $rule, Location $location): bool
    {
        return $this->update($user);
    }

    public function syncLocations(User $user, AlertRule $rule, Collection $locations): bool
    {
        return $this->update($user);
    }

    public function detachLocations(User $user, AlertRule $rule, Location $location): bool
    {
        return $this->update($user);
    }

    public function attachTemplates(User $user, AlertRule $rule, AlertTemplate $template): bool
    {
        return $this->update($user);
    }

    public function syncTemplates(User $user, AlertRule $rule, Collection $templates): bool
    {
        return $this->update($user);
    }

    public function detachTemplates(User $user, AlertRule $rule, AlertTemplate $template): bool
    {
        return $this->update($user);
    }
}
