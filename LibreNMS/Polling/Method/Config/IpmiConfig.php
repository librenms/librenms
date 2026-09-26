<?php

namespace LibreNMS\Polling\Method\Config;

use LibreNMS\Polling\Secrets\Data\IpmiSecretData;

final class IpmiConfig extends PollingMethodConfig
{
    public function __construct(
        bool $enabled,
        bool $affectsAvailability,
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

    public static function default(): static
    {
        return new self(
            enabled: true,
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
    ): self {
        $config = self::default();
        $secretData ??= new IpmiSecretData();

        $config->username = $secretData->username;
        $config->password = $secretData->password;
        $config->kgKey = $secretData->kgKey;
        $config->hostname = ! empty($settings['hostname']) ? (string) $settings['hostname'] : ($fallbackHostname ?: $config->hostname);

        if (isset($settings['port']) && is_numeric($settings['port'])) {
            $config->port = (int) $settings['port'];
        }
        if (isset($settings['ciphersuite']) && is_numeric($settings['ciphersuite'])) {
            $config->cipherSuite = (int) $settings['ciphersuite'];
        }
        if (isset($settings['timeout']) && is_numeric($settings['timeout'])) {
            $config->timeout = (int) $settings['timeout'];
        }
        if (! empty($settings['type'])) {
            $config->type = (string) $settings['type'];
        }

        return $config;
    }

    /**
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
