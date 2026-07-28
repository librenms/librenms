<?php

namespace LibreNMS\Polling\Method;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\DevicePollingMethod;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Interfaces\PollingMethodInterface;
use LibreNMS\Util\Rewrite;

readonly class UnixAgentPollingMethod implements PollingMethodInterface
{
    public function __construct(
        public bool $enabled,
        public bool $affectsAvailability,
        public int $port,
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

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

    public static function fromModel(DevicePollingMethod $method): static
    {
        if ($method->method_type !== PollingMethodType::UnixAgent) {
            throw new \Exception('Invalid polling method type');
        }

        return new static(
            enabled: $method->enabled,
            affectsAvailability: $method->affects_availability,
            port: (int) $method->settings['port'],
        );
    }

    public static function disabled(): static
    {
        return new static(
            enabled: false,
            affectsAvailability: false,
            port: 6556,
        );
    }
}
