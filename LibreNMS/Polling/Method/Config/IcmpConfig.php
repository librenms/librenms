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

    public static function default(): static
    {
        return new self(
            enabled: true,
            affectsAvailability: true,
            ipVersion: 'default',
        );
    }

    public static function fromSettings(array $settings): self
    {
        $config = self::default();

        if (! empty($settings['ip_version'])) {
            $config->ipVersion = (string) $settings['ip_version'];
        }

        return $config;
    }

    /**
     * @return array<string, mixed>
     */
    public function settingsArray(): array
    {
        return [
            'ip_version' => $this->ipVersion,
        ];
    }
}
