<?php

namespace LibreNMS\Polling\Method\Config;

final readonly class IpmiConfig extends PollingMethodConfig
{
    public function __construct(
        public string $hostname,
        public string $username,
        public string $password,
        public string $kgKey,
        public int $port,
        public int $ciphersuite,
        public int $timeout,
        public string $type,
    ) {
    }
}
