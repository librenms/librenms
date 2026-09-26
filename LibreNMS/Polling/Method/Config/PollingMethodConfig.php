<?php

namespace LibreNMS\Polling\Method\Config;

abstract class PollingMethodConfig
{
    public function __construct(
        public bool $enabled = true,
        public bool $affectsAvailability = true,
    ) {
    }

    /**
     * Settings for a newly added polling method.
     * This is the single source of default values for the method.
     */
    abstract public static function default(): static;

    /**
     * Array representation of non-secret settings.
     *
     * @return array<string, mixed>
     */
    abstract public function settingsArray(): array;
}
