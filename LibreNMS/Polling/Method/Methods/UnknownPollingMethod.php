<?php

namespace LibreNMS\Polling\Method\Methods;

use App\Models\Device;
use App\Models\DevicePollingMethod;
use LibreNMS\Polling\Method\Config\PollingMethodConfig;
use LibreNMS\Polling\Method\Config\UnknownConfig;
use LibreNMS\Polling\Method\Definitions\UnknownDefinition;
use LibreNMS\Polling\Method\ProbeResult;

/**
 * Never checked or polled, and does not affect availability.
 *
 * @extends PollingMethod<UnknownConfig>
 */
final class UnknownPollingMethod extends PollingMethod
{
    public function definition(): UnknownDefinition
    {
        return new UnknownDefinition;
    }

    public function defaultAffectsAvailability(): bool
    {
        return false;
    }

    /**
     * @return array{}
     */
    public function defaults(?Device $device = null): array
    {
        return [];
    }

    public function config(Device $device, ?DevicePollingMethod $deviceMethod = null): UnknownConfig
    {
        return new UnknownConfig;
    }

    public function probe(Device $device, PollingMethodConfig $config): ProbeResult
    {
        return ProbeResult::failure(errorMessage: 'Unknown polling method type, it can not be checked');
    }
}
