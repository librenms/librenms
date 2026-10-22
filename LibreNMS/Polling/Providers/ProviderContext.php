<?php

namespace LibreNMS\Polling\Providers;

use App\Models\DevicePollingMethod;
use LibreNMS\OS;
use LibreNMS\Polling\Method\ProbeResult;

/**
 * What a polling method has to build a provider for one poll or discovery of a device.
 * Methods that fetch all their data in the check read it from the result, others query the device when the provider is used.
 */
final readonly class ProviderContext
{
    public function __construct(
        public OS $os,
        public DevicePollingMethod $deviceMethod,
        public ProbeResult $result,
    ) {
    }
}
