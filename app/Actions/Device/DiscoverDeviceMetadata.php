<?php

namespace App\Actions\Device;

use App\Models\Device;
use App\Models\DevicePollingMethod;
use Illuminate\Support\Collection;
use LibreNMS\Polling\Method\PollingMethodRegistry;

readonly class DiscoverDeviceMetadata
{
    public function __construct(
        private PollingMethodRegistry $pollingMethods,
        private ValidateDeviceUniqueness $uniqueness,
    ) {
    }

    /**
     * Query metadata (sysName, OS, etc.) from the device using active polling methods.
     *
     * @param  Collection<int, DevicePollingMethod>  $pollingMethods
     *
     * @throws \LibreNMS\Exceptions\HostSysnameExistsException
     */
    public function execute(Device $device, Collection $pollingMethods): void
    {
        $successfulMethods = $pollingMethods->filter(
            fn (DevicePollingMethod $deviceMethod) => $deviceMethod->enabled && $deviceMethod->last_check_successful
        );

        foreach ($successfulMethods as $deviceMethod) {
            $pollingMethodRegistry = $this->pollingMethods;
            $pollingMethodRegistry->get($deviceMethod->method_type)?->enrichDeviceMetadata($device);
        }

        $this->uniqueness->validateSysName($device);
    }
}
