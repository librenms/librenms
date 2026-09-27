<?php

namespace LibreNMS\Polling\Method\Config;

use App\Facades\LibrenmsConfig;

final class UnixAgentConfig extends PollingMethodConfig
{
    public function __construct(
        bool $enabled,
        bool $affectsAvailability,
        public int $port,
        public int $timeout,
    ) {
        parent::__construct($enabled, $affectsAvailability);
    }

    public static function default(): static
    {
        return new self(
            enabled: true,
            affectsAvailability: false,
            port: (int) LibrenmsConfig::get('unix-agent.port', 6556),
            timeout: (int) LibrenmsConfig::get('unix-agent.connection-timeout', 10),
        );
    }
}
