<?php

namespace App\Actions\Device;

use App\Models\Device;
use LibreNMS\Polling\PollingMethodFactory;

class CheckDeviceAvailability
{
    public function __construct(
        private readonly SetDeviceAvailability $setDeviceAvailability,
        private readonly PollingMethodFactory $pollingMethodFactory,
    ) {
    }

    public function execute(Device $device, bool $commit = false): bool
    {
        $enabledPollingMethods = $device->pollingMethods->filter(fn ($m) => $m->enabled);

        foreach ($enabledPollingMethods as $method) {
            $pollingMethod = $this->pollingMethodFactory->make($method);
            $method->last_check_successful = $pollingMethod->isAvailable($device, $commit);
            $method->last_checked_at = now();
        }

        $this->setDeviceAvailability->execute($device, $commit);

        if ($commit) {
            $enabledPollingMethods->each->save();
            $device->save(); // confirm device is saved
        }

        return $device->status;
    }
}
