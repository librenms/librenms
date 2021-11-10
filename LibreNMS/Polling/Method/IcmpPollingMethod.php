<?php

namespace LibreNMS\Polling\Method;

use App\Models\DevicePollingMethod;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Interfaces\PollingMethod;

readonly class IcmpPollingMethod implements PollingMethod
{
    public function __construct(
        public bool $enabled,
        public bool $affectsAvailability,
    ) {}

    public static function fromModel(DevicePollingMethod $method): self
    {
        if ($method->method_type !== PollingMethodType::Icmp) {
            throw new \Exception('Invalid polling method type');
        }

        return new self(
            enabled: $method->enabled,
            affectsAvailability: $method->affects_availability,
        );
    }

    public static function disabled(): self
    {
        return new self(
            enabled: false,
            affectsAvailability: false,
        );
    }

    public static function getSettingsSchema(): array
    {
        return [];
    }

    public static function getDefaults(): array
    {
        return [
            'affects_availability' => true,
        ];
    }

    public static function getRules(): array
    {
        return [];
    }
}
