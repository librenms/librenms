<?php

namespace LibreNMS\Polling\Method\Methods;

use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\View\FieldSchema\FieldDefinition;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Polling\Method\Config\PollingMethodConfig;
use LibreNMS\Polling\Method\Config\UnixAgentConfig;
use LibreNMS\Polling\Method\ProbeResult;
use LibreNMS\Util\Rewrite;

final class UnixAgentPollingMethod extends PollingMethod
{
    public function icon(): string
    {
        return 'fa-terminal';
    }

    public function defaultConfig(): UnixAgentConfig
    {
        return UnixAgentConfig::default();
    }

    /**
     * @inheritDoc
     */
    public function fields(): array
    {
        return [
            'port' => FieldDefinition::make('port', 'number')
                ->min(1)
                ->max(65535)
                ->rules(['nullable', 'integer', 'min:1', 'max:65535']),

            'timeout' => FieldDefinition::make('timeout', 'number')
                ->min(1)
                ->max(300)
                ->rules(['nullable', 'integer', 'min:1', 'max:300']),
        ];
    }

    /**
     * @param  Device  $device
     * @param  UnixAgentConfig  $config
     * @return ProbeResult
     */
    public function probe(Device $device, PollingMethodConfig $config): ProbeResult
    {
        $agentConfig = $config instanceof UnixAgentConfig ? $config : null;
        $agent_port = $agentConfig->port;
        $timeout = $agentConfig->timeout;
        $poller_target = Rewrite::addIpv6Brackets($device->pollerTarget());

        try {
            $agent = @fsockopen($poller_target, $agent_port, $errno, $errstr, $timeout);
            if ($agent) {
                fclose($agent);

                return ProbeResult::success(['port' => $agent_port, 'timeout' => $timeout]);
            }

            return ProbeResult::failure(['port' => $agent_port, 'timeout' => $timeout], $errstr ?: null);
        } catch (\ErrorException $e) {
            return ProbeResult::failure(['port' => $agent_port, 'timeout' => $timeout], $e->getMessage());
        }
    }

    public function config(DevicePollingMethod $deviceMethod): UnixAgentConfig
    {
        return UnixAgentConfig::fromSettings(
            settings: $deviceMethod->settings ?? [],
            enabled: $deviceMethod->enabled ?? true,
            affectsAvailability: $deviceMethod->affects_availability ?? false,
        );
    }

    public function fallbackConfig(Device $device): UnixAgentConfig
    {
        $method = $device->pollingMethod(PollingMethodType::UnixAgent);
        if ($method) {
            return $this->config($method);
        }

        return UnixAgentConfig::default();
    }
}
