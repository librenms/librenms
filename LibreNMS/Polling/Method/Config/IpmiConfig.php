<?php

namespace LibreNMS\Polling\Method\Config;

final readonly class IpmiConfig extends PollingMethodConfig
{
    public function __construct(
        public string $hostname,
        public string $username,
        public string $password,
        public string $kgKey,
        public ?int $port, // null: ipmitool default
        public int $ciphersuite,
        public ?int $timeout, // null: ipmitool default, it differs by interface
        public string $type,
    ) {
    }
}
