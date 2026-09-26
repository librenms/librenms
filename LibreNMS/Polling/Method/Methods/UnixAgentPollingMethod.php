<?php

namespace LibreNMS\Polling\Method\Methods;

use App\Models\Device;
use App\Models\DevicePollingMethod;
use LibreNMS\Polling\Method\Config\PollingMethodConfig;
use LibreNMS\Polling\Method\Config\UnixAgentConfig;
use LibreNMS\Polling\Method\ProbeResult;
use LibreNMS\Util\Rewrite;

final class UnixAgentPollingMethod extends PollingMethod
{
    public function defaultConfig(): UnixAgentConfig
    {
        return UnixAgentConfig::default();
    }

    public function probe(Device $device, PollingMethodConfig $config): ProbeResult
    {
        assert($config instanceof UnixAgentConfig);

        $stats = ['port' => $config->port, 'timeout' => $config->timeout];
        $poller_target = Rewrite::addIpv6Brackets($device->pollerTarget());

        try {
            $agent = @fsockopen($poller_target, $config->port, $errno, $errstr, $config->timeout);
            if ($agent) {
                fclose($agent);

                return ProbeResult::success($stats);
            }

            return ProbeResult::failure($stats, $errstr ?: null);
        } catch (\ErrorException $e) {
            return ProbeResult::failure($stats, $e->getMessage());
        }
    }

    protected function configFromSettings(DevicePollingMethod $deviceMethod): UnixAgentConfig
    {
        return UnixAgentConfig::fromSettings($deviceMethod->settings ?? []);
    }
}
