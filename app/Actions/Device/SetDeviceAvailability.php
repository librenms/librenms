<?php

namespace App\Actions\Device;

use App\Models\Device;
use LibreNMS\Enum\PollingMethodType;

class SetDeviceAvailability
{
    /**
     * Set status and status_reason fields based on availability results.
     * Does not persist to the database unless $commit is true.
     *
     * @param  Device  $device
     * @param  bool  $commit  Save changes to the database
     * @return bool true if the status changed
     */
    public function execute(Device $device, bool $commit = true): bool
    {
        $failedAvailabilityMethods = $device->polling()->failedAvailabilityChecks();

        $device->status = $failedAvailabilityMethods->isEmpty();
        $device->status_reason = $failedAvailabilityMethods->map(fn ($m) => $m->method_type->value)->implode(',');
        $device->mtu_status = $device->pollingMethod(PollingMethodType::Mtu)?->enabled ? ($device->pollingMethod(PollingMethodType::Mtu)->last_check_successful ?? true) : true;

        $changed = $device->isDirty('status') || $device->isDirty('mtu_status');

        if ($commit) {
            $device->save();
        }

        return $changed;
    }
}
