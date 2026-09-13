<?php

namespace LibreNMS\Polling\Method\Probe;

use App\Models\Device;
use LibreNMS\Data\Source\Ipmitool;
use LibreNMS\Interfaces\PollingMethodProbeInterface;

final readonly class IpmiProbe implements PollingMethodProbeInterface
{
    public function check(Device $device): ProbeResult
    {
        $ipmi = Ipmitool::init($device);

        if (! $ipmi) {
            return ProbeResult::failure();
        }

        try {
            $ipmi->command(['power', 'status']);

            return ProbeResult::success();
        } catch (\Throwable) {
            return ProbeResult::failure();
        }
    }
}
