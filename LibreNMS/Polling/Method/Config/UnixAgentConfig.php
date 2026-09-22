<?php

namespace LibreNMS\Polling\Method\Config;

use App\Facades\LibrenmsConfig;
use App\Models\DevicePollingMethod;
use LibreNMS\Enum\PollingMethodType;

final class UnixAgentConfig extends PollingMethodConfig
{
    public function __construct(
        public bool $enabled,
        public bool $affectsAvailability,
        public int $port,
        public int $timeout,
    ) {
        parent::__construct($enabled, $affectsAvailability);
    }

    public function isValid(): bool
    {
        return true;
    }

    public static function fromPollingMethod(DevicePollingMethod $method): self
    {
        if ($method->method_type !== PollingMethodType::UnixAgent) {
            throw new \Exception('Invalid polling method type');
        }

        $settings = $method->settings ?? [];

        return new self(
            enabled: $method->enabled ?? true,
            affectsAvailability: $method->affects_availability ?? false,
            port: (int) ($settings['port'] ?? LibrenmsConfig::get('unix-agent.port', 6556)),
            timeout: (int) ($settings['timeout'] ?? LibrenmsConfig::get('unix-agent.connection-timeout', 10)),
        );
    }
}
