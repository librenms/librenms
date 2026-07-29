<?php

namespace LibreNMS\Interfaces;

use App\Models\Device;
use App\Models\DevicePollingMethod;

interface PollingMethodConfigInterface
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
     * Create an instance of this Polling Method Config from a DevicePollingMethod model
     */
    public static function fromModel(DevicePollingMethod $method): static;
}
