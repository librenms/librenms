<?php

namespace App\Actions\Device;

use App\Models\Device;
use App\Models\DevicePollingMethod;
use Illuminate\Support\Collection;

class DiscoverDeviceMetadata
{
    public function __construct(
        private readonly \LibreNMS\Polling\Method\PollingMethodRegistry $registry,
        private readonly ValidateDeviceUniqueness $uniqueness = new ValidateDeviceUniqueness,
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
            fn (DevicePollingMethod $method) => $method->enabled && $method->last_check_successful
        );

        foreach ($successfulMethods as $method) {
            $this->registry->get($method->method_type)?->enrichDeviceMetadata($device);
        }

        $this->uniqueness->validateSysName($device);
    }
}
