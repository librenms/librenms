<?php

namespace LibreNMS\Polling\Method\Config;

abstract class PollingMethodConfig
{
    public function __construct(
        public bool $enabled = true,
        public bool $affectsAvailability = true,
    ) {
    }

    abstract public function isValid(): bool;

    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}
