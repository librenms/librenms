<?php

namespace LibreNMS\Polling\Method\Probe;

use App\Models\Device;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Interfaces\PollingMethodProbeInterface;
use LibreNMS\Polling\Method\Config\UnixAgentConfig;
use LibreNMS\Util\Rewrite;

final readonly class UnixAgentProbe implements PollingMethodProbeInterface
{
    public function check(Device $device): ProbeResult
    {
        /** @var UnixAgentConfig|null $config */
        $config = $device->pollingMethod(PollingMethodType::UnixAgent)?->toConfig();
        $agent_port = $config ? $config->port : 6556;
        $timeout = $config ? $config->timeout : 10;
        $poller_target = Rewrite::addIpv6Brackets($device->pollerTarget());

        try {
            $agent = @fsockopen($poller_target, $agent_port, $errno, $errstr, $timeout);
            if ($agent) {
                fclose($agent);

                return ProbeResult::success(['port' => $agent_port, 'timeout' => $timeout]);
            }
        } catch (\Throwable) {
            // return failure
        }

        return ProbeResult::failure(['port' => $agent_port, 'timeout' => $timeout]);
    }
}
