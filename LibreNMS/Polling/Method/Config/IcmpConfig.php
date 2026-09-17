<?php

namespace LibreNMS\Polling\Method\Config;

use App\Models\DevicePollingMethod;
use LibreNMS\Enum\PollingMethodType;

class IcmpConfig extends PollingMethodConfig
{
    public function __construct(
        public bool $enabled,
        public bool $affectsAvailability,
    ) {
        parent::__construct($enabled, $affectsAvailability);
    }

    public function isValid(): bool
    {
        return true;
    }

    public static function fromPollingMethod(DevicePollingMethod $method): static
    {
        if ($method->method_type !== PollingMethodType::Icmp) {
            throw new \Exception('Invalid polling method type');
        }

        return new static(
            enabled: $method->enabled ?? true,
            affectsAvailability: $method->affects_availability ?? false,
        );
    }
}
