<?php

namespace LibreNMS\Polling\Method\Config;

final readonly class RedfishConfig extends PollingMethodConfig
{
    public function __construct(
        public string $hostname,
        public string $username,
        public string $password,
        public string $scheme,
        public ?int $port, // null: scheme default (443/80)
        public bool $insecure, // skip TLS certificate verification, self-signed certificates are common
        public int $timeout,
    ) {
    }
}
