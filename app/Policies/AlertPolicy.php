<?php

namespace App\Policies;

use App\Facades\Permissions;
use App\Models\Alert;
use App\Models\User;

class AlertPolicy
{
    use ChecksGlobalPermissions;
    use ResolvesPolicyTargets;

    /**
     * @param  Alert|array  $alert
     */
    private function castAlertModel(Alert|array $alert): Alert
    {
        /** @var Alert $model */
        $model = $this->castToModel($alert, Alert::class);

        // Authorization only needs device_id; ensure it's present even if mass assignment blocks it.
        if (is_array($alert) && array_key_exists('device_id', $alert)) {
            $model->device_id = $alert['device_id'];
        }

        return $model;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $this->hasGlobalPermission($user, 'viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Alert|array $alert): bool
    {
        if ($this->hasGlobalPermission($user, 'viewAny')) {
            return true;
        }

        $alert = $this->castAlertModel($alert);
        $deviceId = is_numeric($alert->device_id) ? (int) $alert->device_id : null;

        return $this->hasGlobalPermission($user, 'view')
            && $deviceId !== null
            && Permissions::canAccessDevice($deviceId, $user);
    }

    public function detail(User $user, Alert|array $alert): bool
    {
        $alert = $this->castAlertModel($alert);
        $deviceId = is_numeric($alert->device_id) ? (int) $alert->device_id : null;

        return $this->hasGlobalPermission($user, 'detail') &&
            $deviceId !== null &&
            Permissions::canAccessDevice($deviceId, $user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Alert|array $alert): bool
    {
        $alert = $this->castAlertModel($alert);
        $deviceId = is_numeric($alert->device_id) ? (int) $alert->device_id : null;

        return $this->hasGlobalPermission($user, 'update') &&
            $deviceId !== null &&
            Permissions::canAccessDevice($deviceId, $user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Alert|array $alert): bool
    {
        $alert = $this->castAlertModel($alert);
        $deviceId = is_numeric($alert->device_id) ? (int) $alert->device_id : null;

        return $this->hasGlobalPermission($user, 'delete') &&
            $deviceId !== null &&
            Permissions::canAccessDevice($deviceId, $user);
    }
}
