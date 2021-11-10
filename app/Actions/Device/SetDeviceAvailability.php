<?php

namespace App\Actions\Device;

use App\Models\Device;

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

        $changed = $device->isDirty('status');

        if ($commit) {
            $device->save();
        }

        return $changed;
    }
}
