<?php

namespace LibreNMS\Polling\Method\Methods;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\DevicePollingMethod;
use Illuminate\Support\Facades\Log;
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

    /**
     * Connecting runs the whole agent, so the check fetches its output for the unix-agent module (output and time stats).
     */
    public function probe(Device $device, PollingMethodConfig $config): ProbeResult
    {
        assert($config instanceof UnixAgentConfig);

        $stats = ['port' => $config->port, 'timeout' => $config->timeout];
        $start = microtime(true);

        try {
            $socket = @fsockopen(Rewrite::addIpv6Brackets($device->pollerTarget()), $config->port, $errno, $errstr, $config->timeout);
        } catch (\ErrorException $e) {
            return ProbeResult::failure($stats, $e->getMessage()); // usually connection timed out
        }

        if (! $socket) {
            return ProbeResult::failure($stats, $errstr ?: null);
        }

        stream_set_timeout($socket, (int) LibrenmsConfig::get('unix-agent.read-timeout'));

        $output = '';
        $info = stream_get_meta_data($socket);
        while (! feof($socket) && ! $info['timed_out']) {
            $output .= fgets($socket, 128);
            $info = stream_get_meta_data($socket);
        }
        fclose($socket);

        if ($info['timed_out']) {
            Log::error("Connection to UNIX agent timed out during fetch on port $config->port");
        }

        return ProbeResult::success($stats + [
            'output' => $output,
            'time' => round((microtime(true) - $start) * 1000),
        ]);
    }
}
