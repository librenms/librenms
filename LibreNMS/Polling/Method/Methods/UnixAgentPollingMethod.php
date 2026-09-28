<?php

namespace LibreNMS\Polling\Method\Methods;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\DevicePollingMethod;
use LibreNMS\Polling\Method\Config\PollingMethodConfig;
use LibreNMS\Polling\Method\Config\UnixAgentConfig;
use LibreNMS\Polling\Method\Definitions\UnixAgentDefinition;
use LibreNMS\Polling\Method\ProbeResult;
use LibreNMS\Util\Rewrite;

/**
 * @extends PollingMethod<UnixAgentConfig>
 */
final class UnixAgentPollingMethod extends PollingMethod
{
    public function definition(): UnixAgentDefinition
    {
        return new UnixAgentDefinition;
    }

    public function defaultAffectsAvailability(): bool
    {
        return false;
    }

    /**
     * @return array{port: int, timeout: int}
     */
    public function defaults(?Device $device = null): array
    {
        return [
            'port' => (int) LibrenmsConfig::get('unix-agent.port', 6556),
            'timeout' => (int) LibrenmsConfig::get('unix-agent.connection-timeout', 10),
        ];
    }

    public function config(Device $device, ?DevicePollingMethod $deviceMethod = null): UnixAgentConfig
    {
        $settings = ($deviceMethod->settings ?? []) + $this->defaults($device);

        return new UnixAgentConfig(
            port: $settings['port'],
            timeout: $settings['timeout'],
        );
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
}
