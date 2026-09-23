<?php

namespace App\Actions\Device;

use App\Models\Device;
use App\Models\Eventlog;
use LibreNMS\Enum\Severity;
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
            try {
                $method = $this->pollingMethods->require($deviceMethod->method_type);
                $result = $method->probe($device);

                $deviceMethod->last_check_successful = $result->isSuccess();
                $deviceMethod->last_checked_at = now();

                $method->onProbeComplete($device, $result, $commit);
            } catch (\LibreNMS\Exceptions\SecretDecryptionException) {
                $deviceMethod->last_check_successful = false;
                $deviceMethod->last_checked_at = now();
                Eventlog::log("Failed to decrypt credentials for {$deviceMethod->method_type->value} polling. Verify that APP_KEY matches the primary installation.", $device, 'auth', Severity::Error);
            }
        }

        $this->setDeviceAvailability->execute($device, $commit);

        if ($commit) {
            $enabledPollingMethods->each->save();
            $device->save(); // confirm device is saved
        }

        return $device->status;
    }
}
