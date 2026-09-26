<?php

namespace LibreNMS\Polling\Method\Config;

use App\Facades\LibrenmsConfig;

final class UnixAgentConfig extends PollingMethodConfig
{
    public function __construct(
        public bool $enabled,
        public bool $affectsAvailability,
        public int $port,
        public int $timeout,
    ) {
        parent::__construct($enabled, $affectsAvailability);
    }

    public function isValid(): bool
    {
        return true;
    }

    public static function default(): self
    {
        return new self(
            enabled: false,
            affectsAvailability: false,
            port: (int) LibrenmsConfig::get('unix-agent.port', 6556),
            timeout: (int) LibrenmsConfig::get('unix-agent.connection-timeout', 10),
        );
    }

    public static function fromSettings(
        array $settings,
        bool $enabled = true,
        bool $affectsAvailability = false,
    ): self {
        $default = self::default();

        return new self(
            enabled: $enabled,
            affectsAvailability: $affectsAvailability,
            port: isset($settings['port']) && is_numeric($settings['port']) ? (int) $settings['port'] : $default->port,
            timeout: isset($settings['timeout']) && is_numeric($settings['timeout']) ? (int) $settings['timeout'] : $default->timeout,
        );
    }

    /**
     * Array representation of non-secret settings.
     *
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
