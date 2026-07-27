<?php

namespace LibreNMS\Interfaces;

use App\Models\Device;
use App\Models\DevicePollingMethod;

interface PollingMethodInterface
{
    /**
     * Check if the polling method is available/reachable for the device.
     */
    public function isAvailable(Device $device, bool $commit = false): bool;

    /**
     * Check if the polling method is enabled.
     */
    public function isEnabled(): bool;

    /**
     * Get a new instance that is disabled
     *
     * @return static
     */
    public static function disabled(): static;

    /**
     * Create an instance of this Polling Method from a DevicePollingMethod model
     */
    public static function fromModel(DevicePollingMethod $method): static;

    /**
     * Create or update the DevicePollingMethod row (and its associated Secret, if applicable)
     * for the given device.  Settings and secretData are merged into existing values.
     *
     * @param  array<string, mixed>  $settings  Non-sensitive per-device settings.
     * @param  array<string, mixed>  $secretData  Credential fields (ignored for methods with no secret).
     */
    public static function save(
        Device $device,
        array $settings = [],
        array $secretData = [],
        bool $enabled = true,
        bool $affectsAvailability = false,
    ): DevicePollingMethod;
}
