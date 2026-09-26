<?php

namespace LibreNMS\Polling\Method\Config;

use LibreNMS\Polling\Secrets\Data\IpmiSecretData;

final class IpmiConfig extends PollingMethodConfig
{
    public function __construct(
        public bool $enabled,
        public bool $affectsAvailability,
        public string $username,
        public string $password,
        public string $kgKey,
        public string $hostname,
        public int $port,
        public int $cipherSuite,
        public int $timeout,
        public string $type,
    ) {
        parent::__construct($enabled, $affectsAvailability);
    }

    public function isValid(): bool
    {
        return ! empty($this->username) && ! empty($this->password);
    }

    public static function default(): self
    {
        return new self(
            enabled: false,
            affectsAvailability: false,
            username: '',
            password: '',
            kgKey: '',
            hostname: '',
            port: 623,
            cipherSuite: 0,
            timeout: 3,
            type: '',
        );
    }

    public static function fromSettings(
        array $settings,
        ?IpmiSecretData $secretData = null,
        ?string $fallbackHostname = null,
        bool $enabled = true,
        bool $affectsAvailability = false,
    ): self {
        $default = self::default();
        $secretData ??= new IpmiSecretData();

        $hostname = ! empty($settings['hostname'])
            ? (string) $settings['hostname']
            : ($fallbackHostname ?: $default->hostname);

        return new self(
            enabled: $enabled,
            affectsAvailability: $affectsAvailability,
            username: $secretData->username,
            password: $secretData->password,
            kgKey: $secretData->kgKey,
            hostname: $hostname,
            port: isset($settings['port']) && is_numeric($settings['port']) ? (int) $settings['port'] : $default->port,
            cipherSuite: isset($settings['ciphersuite']) && is_numeric($settings['ciphersuite']) ? (int) $settings['ciphersuite'] : $default->cipherSuite,
            timeout: isset($settings['timeout']) && is_numeric($settings['timeout']) ? (int) $settings['timeout'] : $default->timeout,
            type: ! empty($settings['type']) ? (string) $settings['type'] : $default->type,
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
            'hostname' => $this->hostname,
            'port' => $this->port,
            'ciphersuite' => $this->cipherSuite,
            'timeout' => $this->timeout,
        ];
    }
}
