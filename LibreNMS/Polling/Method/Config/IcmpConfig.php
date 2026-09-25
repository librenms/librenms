<?php

namespace LibreNMS\Polling\Method\Config;

use App\Models\DevicePollingMethod;
use LibreNMS\Enum\PollingMethodType;

final class IcmpConfig extends PollingMethodConfig
{
    public function __construct(
        bool $enabled = true,
        bool $affectsAvailability = true,
        public string $ipVersion = 'default',
    ) {
        parent::__construct($enabled, $affectsAvailability);
    }

    public function isValid(): bool
    {
        return true;
    }

    public static function fromPollingMethod(DevicePollingMethod $deviceMethod): self
    {
        if ($deviceMethod->method_type !== PollingMethodType::Icmp) {
            throw new \Exception('Invalid polling method type');
        }

        return new self(
            enabled: $deviceMethod->enabled ?? true,
            affectsAvailability: $deviceMethod->affects_availability ?? false,
            ipVersion: $deviceMethod->settings['ip_version'] ?? 'default',
        );
    }
}
