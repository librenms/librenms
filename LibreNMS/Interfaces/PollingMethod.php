<?php

namespace LibreNMS\Interfaces;

use App\Models\Device;

interface PollingMethod
{
    /**
     * Check if the polling method is available/reachable for the device.
     */
    public function isAvailable(Device $device, bool $commit = false): bool;

    /**
     * UI/form schema for device-specific settings.
     * @return array<string, array{type: string, options?: array<string,string>, visible_if: array}>
     */
    public static function getSettingsSchema(): array;

    /**
     * Defaults for polling method per-device settings
     * @return array<string, mixed>
     */
    public static function getDefaults(): array;

    /**
     * Validation rules for polling method per-device settings
     * @return array<string, array|string>
     */
    public static function getRules(): array;
}
