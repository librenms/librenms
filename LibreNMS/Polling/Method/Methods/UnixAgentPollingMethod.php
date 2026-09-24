<?php

namespace LibreNMS\Polling\Method\Methods;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\View\FieldSchema\FieldDefinition;
use LibreNMS\Polling\Method\Config\UnixAgentConfig;
use LibreNMS\Polling\Method\ProbeResult;
use LibreNMS\Util\Rewrite;

final class UnixAgentPollingMethod extends PollingMethod
{
    public function icon(): string
    {
        return 'fa-terminal';
    }

    public function defaultAffectsAvailability(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function fields(): array
    {
        return [
            'port' => FieldDefinition::make('port', 'number')
                ->default(fn () => (int) LibrenmsConfig::get('unix-agent.port', 6556))
                ->min(1)
                ->max(65535)
                ->rules(['nullable', 'integer', 'min:1', 'max:65535'])
                ->cast('int'),

            'timeout' => FieldDefinition::make('timeout', 'number')
                ->default(fn () => (int) LibrenmsConfig::get('unix-agent.connection-timeout', 10))
                ->min(1)
                ->max(300)
                ->rules(['nullable', 'integer', 'min:1', 'max:300'])
                ->cast('int'),
        ];
    }

    public function probe(Device $device): ProbeResult
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

            return ProbeResult::failure(['port' => $agent_port, 'timeout' => $timeout], $errstr ?: null);
        } catch (\ErrorException $e) {
            return ProbeResult::failure(['port' => $agent_port, 'timeout' => $timeout], $e->getMessage());
        }
    }

    public function config(DevicePollingMethod $deviceMethod): UnixAgentConfig
    {
        return UnixAgentConfig::fromPollingMethod($deviceMethod);
    }

    public function fallbackConfig(Device $device): UnixAgentConfig
    {
        return new UnixAgentConfig(
            enabled: false,
            affectsAvailability: false,
            port: (int) LibrenmsConfig::get('unix-agent.port', 6556),
            timeout: (int) LibrenmsConfig::get('unix-agent.connection-timeout', 10),
        );
    }
}
