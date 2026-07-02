<?php

namespace LibreNMS\Polling\Method;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\DevicePollingMethod;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Interfaces\PollingMethod;
use LibreNMS\Util\Rewrite;

readonly class UnixAgentPollingMethod implements PollingMethod
{
    public function __construct(
        public bool $enabled,
        public bool $affectsAvailability,
        public int $port,
    ) {}

    public function isAvailable(Device $device, bool $commit = false): bool
    {
        $agent_port = $device->getAttrib('override_Unixagent_port');
        if (empty($agent_port)) {
            $agent_port = LibrenmsConfig::get('unix-agent.port');
        }

        $poller_target = Rewrite::addIpv6Brackets($device->pollerTarget());
        $timeout = LibrenmsConfig::get('unix-agent.connection-timeout', 10);

        try {
            $agent = @fsockopen($poller_target, (int) $agent_port, $errno, $errstr, $timeout);
            if ($agent) {
                fclose($agent);
                return true;
            }
        } catch (\Throwable) {
            // return false
        }

        return false;
    }

    public static function fromModel(DevicePollingMethod $method): self
    {
        if ($method->method_type !== PollingMethodType::UnixAgent) {
            throw new \Exception('Invalid polling method type');
        }

        $port = (int) ($method->settings['port'] ?? LibrenmsConfig::get('unix-agent.port', 6556));

        return new self(
            enabled: $method->enabled,
            affectsAvailability: $method->affects_availability,
            port: $port,
        );
    }

    public static function disabled(): self
    {
        return new self(
            enabled: false,
            affectsAvailability: false,
            port: 6556,
        );
    }

    public static function getSettingsSchema(): array
    {
        return [
            'port' => [
                'type' => 'number',
                'default' => 6556,
                'min' => 1,
                'max' => 65535,
            ],
        ];
    }

    public static function getDefaults(): array
    {
        return [
            'port' => 6556,
        ];
    }

    public static function getRules(): array
    {
        return [
            'port' => ['nullable', 'integer', 'min:1', 'max:65535'],
        ];
    }
}
