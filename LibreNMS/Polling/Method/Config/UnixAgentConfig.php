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

    public static function fromSettings(array $settings): self
    {
        $config = self::default();

        if (isset($settings['port']) && is_numeric($settings['port'])) {
            $config->port = (int) $settings['port'];
        }
        if (isset($settings['timeout']) && is_numeric($settings['timeout'])) {
            $config->timeout = (int) $settings['timeout'];
        }

        return $config;
    }

    /**
     * @return array<string, mixed>
     */
    public function settingsArray(): array
    {
        return [
            'port' => $this->port,
            'timeout' => $this->timeout,
        ];
    }
}
