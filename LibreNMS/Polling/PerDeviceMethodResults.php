<?php

/**
 * PerDeviceMethodResults.php
 *
 * The result of checking each polling method during one poll or discovery of a device.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Polling;

use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\Models\Eventlog;
use Illuminate\Support\Facades\Log;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\Severity;
use LibreNMS\Exceptions\SecretDecryptionException;
use LibreNMS\Polling\Method\PollingMethodRegistry;
use LibreNMS\Polling\Method\ProbeResult;
use Throwable;

/**
 * Each method is checked at most once: by the availability check when it affects availability,
 * otherwise by the first module that needs it, and the following modules use that result.
 */
final class PerDeviceMethodResults
{
    /** @var array<string, ProbeResult> keyed by method type */
    private array $results = [];

    public function __construct(
        public readonly Device $device,
    ) {
    }

    /**
     * Whether the method is enabled and its check succeeded. The first request for a method checks it.
     */
    public function isAvailable(PollingMethodType $type): bool
    {
        return (bool) $this->result($type)?->isSuccess();
    }

    /**
     * The result of the method's check, with any data the check fetched. The first request for a method checks it.
     * Null when the method is not enabled.
     */
    public function result(PollingMethodType $type): ?ProbeResult
    {
        $deviceMethod = $this->device->pollingMethod($type);
        if ($deviceMethod === null || ! $deviceMethod->enabled) {
            return null;
        }

        return $this->results[$type->value] ??= $this->check($deviceMethod);
    }

    /**
     * Store the last check of each method checked so far.
     */
    public function saveCheckedMethods(): void
    {
        if (! $this->device->exists) {
            return;
        }

        foreach (array_keys($this->results) as $type) {
            $this->device->pollingMethod(PollingMethodType::from($type))?->save();
        }
    }

    private function check(DevicePollingMethod $deviceMethod): ProbeResult
    {
        $method = app(PollingMethodRegistry::class)->get($deviceMethod->method_type);

        try {
            $result = $method->probe($this->device, $method->config($this->device, $deviceMethod));
        } catch (Throwable $e) {
            // A method that cannot be checked counts as failed
            $result = ProbeResult::failure(errorMessage: $this->checkError($deviceMethod->method_type->value, $e));
        }

        $deviceMethod->last_check_successful = $result->isSuccess();
        $deviceMethod->last_check_message = $result->isSuccess() ? null : (implode("\n", $result->reasons()) ?: null);

        return $result;
    }

    /**
     * Log an error that kept the method from being checked, returns the message for the user.
     */
    private function checkError(string $type, Throwable $e): string
    {
        if ($e instanceof SecretDecryptionException) {
            Log::error("Failed to decrypt credentials for $type polling on {$this->device->hostname}: {$e->getMessage()}");
            $message = "Failed to decrypt credentials for $type polling. Verify that APP_KEY matches the primary installation.";
            $logType = 'auth';
        } else {
            report($e);
            $message = "Error checking $type availability: " . class_basename($e) . '. Check log file for more details.';
            $logType = $type;
        }

        if ($this->device->exists) {
            Eventlog::log($message, $this->device, $logType, Severity::Error);
        }

        return $message;
    }
}
