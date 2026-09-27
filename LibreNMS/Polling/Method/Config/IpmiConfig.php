<?php

namespace LibreNMS\Polling\Method\Config;

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
        public int $ciphersuite,
        public int $timeout,
        public string $type,
    ) {
        parent::__construct($enabled, $affectsAvailability);
    }

    public static function default(string $hostname = ''): static
    {
        return new self(
            enabled: true,
            affectsAvailability: false,
            username: '',
            password: '',
            kgKey: '',
            hostname: $hostname,
            port: 623,
            ciphersuite: 0,
            timeout: 3,
            type: '', // detected
        );
    }
}
