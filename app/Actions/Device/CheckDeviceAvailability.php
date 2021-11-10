<?php

namespace App\Actions\Device;

use App\Models\Device;
use App\Models\Eventlog;
use Illuminate\Support\Facades\Log;
use LibreNMS\Enum\Severity;
use LibreNMS\Exceptions\SecretDecryptionException;
use LibreNMS\Polling\Method\PollingMethodRegistry;
use Throwable;

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

            try {
                $result = $method->probe($device, $method->config($deviceMethod));
                $deviceMethod->last_check_successful = $result->isSuccess();
                $method->onProbeComplete($device, $result, $commit);
            } catch (Throwable $e) {
                // A method that cannot be checked counts as failed, the other methods are still checked
                $deviceMethod->last_check_successful = false;
                $this->logCheckError($device, $deviceMethod->method_type->value, $e);
            }

            $deviceMethod->last_checked_at = now();
        }

        if ($commit) {
            $enabledPollingMethods->each->save();
        }

        $this->setDeviceAvailability->execute($device, $commit);

        return $device->status;
    }

    private function logCheckError(Device $device, string $type, Throwable $e): void
    {
        if ($e instanceof SecretDecryptionException) {
            Log::error("Failed to decrypt credentials for $type polling on $device->hostname: {$e->getMessage()}");
            $message = "Failed to decrypt credentials for $type polling. Verify that APP_KEY matches the primary installation.";
            $type = 'auth';
        } else {
            report($e);
            $message = "Error checking $type availability: " . class_basename($e) . '. Check log file for more details.';
        }

        if ($device->exists) {
            Eventlog::log($message, $device, $type, Severity::Error);
        }
    }
}
