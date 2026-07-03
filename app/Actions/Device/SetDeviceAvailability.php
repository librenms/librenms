<?php

namespace App\Actions\Device;

use App\Models\Device;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class SetDeviceAvailability
{
    public function __construct(
        private readonly UpdateDeviceOutage $updateDeviceOutage,
    ) {
    }

    /**
     * @param  Device  $device
     * @param  bool  $commit  Save changes to the database
     * @return bool true if the status changed
     */
    public function execute(Device $device, bool $commit = true): bool {
        if ($device->exists || $device->relationLoaded('pollingMethods')) {
            $failedAvailabilityMethods = $device->pollingMethods
                ->filter(fn ($method) => $method->enabled && $method->affects_availability && ! $method->last_check_successful);
        } else {
            $failedAvailabilityMethods = new Collection;
        }

        $device->status = $failedAvailabilityMethods->isEmpty();
        $device->status_reason = $failedAvailabilityMethods->map(fn ($m) => $m->method_type->value)->implode(',');

        $changed = $device->isDirty('status');

        if ($commit) {
            $device->save();
            Log::debug('Device availability updated for ' . $device->hostname . ' to ' . ($device->status ? 'up' : 'down') . ' due to ' . $device->status_reason);
            $this->updateDeviceOutage->execute($device, $device->status);
        }

        return $changed;
    }
}
