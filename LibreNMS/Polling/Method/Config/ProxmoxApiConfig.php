<?php

namespace LibreNMS\Polling\Method\Config;

final readonly class ProxmoxApiConfig extends PollingMethodConfig
{
    public function __construct(
        public string $hostname,
        public int $port,
        public string $tokenId,
        public string $tokenSecret,
        public int $timeout,
        public bool $verifySsl,
    ) {
    }
}
