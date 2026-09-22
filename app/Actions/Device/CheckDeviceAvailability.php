<?php

namespace App\Actions\Device;

use App\Models\Device;
use App\Models\Eventlog;
use LibreNMS\Enum\Severity;

readonly class CheckDeviceAvailability
{
    public function __construct(
        private SetDeviceAvailability $setDeviceAvailability,
        private \LibreNMS\Polling\Method\PollingMethodRegistry $registry,
    ) {
    }

    public function execute(Device $device, bool $commit = false): bool
    {
        $enabledPollingMethods = $device->pollingMethods->filter(fn ($m) => $m->enabled);

        foreach ($enabledPollingMethods as $method) {
            try {
                $definition = $this->registry->require($method->method_type);
                $result = $definition->probe()->check($device);

                $method->last_check_successful = $result->isSuccess();
                $method->last_checked_at = now();

                $definition->onProbeComplete($device, $result, $commit);
            } catch (\LibreNMS\Exceptions\SecretDecryptionException) {
                $method->last_check_successful = false;
                $method->last_checked_at = now();
                Eventlog::log("Failed to decrypt credentials for {$method->method_type->value} polling. Verify that APP_KEY matches the primary installation.", $device, 'auth', Severity::Error);
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
