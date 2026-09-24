<?php

namespace App\Actions\Device;

use App\Models\Device;
use LibreNMS\Polling\Method\PollingMethodRegistry;

readonly class CheckDeviceAvailability
{
    public function __construct(
        private SetDeviceAvailability $setDeviceAvailability,
        private PollingMethodRegistry $pollingMethods,
    ) {
    }

    public function execute(Device $device, bool $commit = false): bool
    {
        $enabledPollingMethods = $device->pollingMethods->filter(fn ($m) => $m->enabled);

        foreach ($enabledPollingMethods as $deviceMethod) {
            $method = $this->pollingMethods->require($deviceMethod->method_type);
            $result = $method->probe($device);

            $deviceMethod->last_check_successful = $result->isSuccess();
            $deviceMethod->last_checked_at = now();

            $method->onProbeComplete($device, $result, $commit);
        }

        $this->setDeviceAvailability->execute($device, $commit);

        if ($commit) {
            $enabledPollingMethods->each->save();
            $device->save(); // confirm device is saved
        }

        return $device->status;
    }
}
