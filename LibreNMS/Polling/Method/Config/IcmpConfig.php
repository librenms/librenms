<?php

namespace LibreNMS\Polling\Method\Config;

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
}
