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

    public static function default(): self
    {
        return new self(
            enabled: true,
            affectsAvailability: true,
            ipVersion: 'default',
        );
    }

    public static function fromSettings(
        array $settings,
        bool $enabled = true,
        bool $affectsAvailability = true,
    ): self {
        $default = self::default();

        return new self(
            enabled: $enabled,
            affectsAvailability: $affectsAvailability,
            ipVersion: ! empty($settings['ip_version']) ? (string) $settings['ip_version'] : $default->ipVersion,
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
            'ip_version' => $this->ipVersion,
        ];
    }
}
