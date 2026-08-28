<?php

namespace LibreNMS\Polling\Method\Config;

use App\Models\DevicePollingMethod;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Interfaces\PollingMethodConfigInterface;

readonly class UnixAgentConfig implements PollingMethodConfigInterface
{
    public function __construct(
        public bool $enabled,
        public bool $affectsAvailability,
        public int $port,
        public int $timeout,
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public static function fromModel(DevicePollingMethod $method): static
    {
        if ($method->method_type !== PollingMethodType::UnixAgent) {
            throw new \Exception('Invalid polling method type');
        }

        $definition = PollingMethodType::UnixAgent->definition();
        $settings = $definition->resolveValues($method->settings ?? []);

        return new static(
            enabled: $method->enabled,
            affectsAvailability: $method->affects_availability,
            port: $settings['port'],
            timeout: $settings['timeout'],
        );
    }
}
