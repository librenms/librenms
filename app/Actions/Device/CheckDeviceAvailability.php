<?php

namespace App\Actions\Device;

use App\Models\DevicePollingMethod;
use Illuminate\Support\Facades\Log;
use LibreNMS\Polling\PerDeviceMethodResults;

readonly class CheckDeviceAvailability
{
    public function __construct(
        private SetDeviceAvailability $setDeviceAvailability,
    ) {
    }

    /**
     * Check the methods that affect availability and set the device status.
     * Other methods are checked by the first module that needs them.
     */
    public function execute(PerDeviceMethodResults $methodResults, bool $commit = false): bool
    {
        $device = $methodResults->device;

        if ($device->pollingMethods->isEmpty()) {
            Log::debug("No polling methods for $device->hostname, availability is not checked");

            return $device->status;
        }

        $availabilityMethods = $device->pollingMethods
            ->filter(fn (DevicePollingMethod $deviceMethod): bool => $deviceMethod->enabled && $deviceMethod->affects_availability);

        foreach ($availabilityMethods as $deviceMethod) {
            $methodResults->result($deviceMethod->method_type); // checks the method
        }

        if ($commit) {
            $availabilityMethods->each->save();
        }

        $this->setDeviceAvailability->execute($device, $commit);

        return $device->status;
    }
}
