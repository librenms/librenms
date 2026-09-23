<?php

namespace LibreNMS\Polling\Method\Probe;

use App\Models\Device;
use LibreNMS\Util\Rewrite;

class UnixAgentProbe extends PollingMethodProbe
{
    public function check(Device $device): ProbeResult
    {
        $config = $device->pollingMethodFor()->unixAgent();
        $agent_port = $config->port;
        $timeout = $config->timeout;
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
