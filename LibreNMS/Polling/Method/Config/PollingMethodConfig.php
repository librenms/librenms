<?php

namespace LibreNMS\Polling\Method\Config;

use App\Models\DevicePollingMethod;

abstract class PollingMethodConfig
{
    public function __construct(
        public bool $enabled = true,
        public bool $affectsAvailability = true,
    ) {
    }

    abstract public static function fromPollingMethod(DevicePollingMethod $deviceMethod): self;

    abstract public function isValid(): bool;

    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}
