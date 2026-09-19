<?php

namespace App\Actions\Device;

use App\Models\Device;
use App\Models\DevicePollingMethod;
use Illuminate\Support\Collection;

class DiscoverDeviceMetadata
{
    public function __construct(
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
            $method->method_type->definition()->enrichDeviceMetadata($device);
        }

        $this->uniqueness->validateSysName($device);
    }
}
