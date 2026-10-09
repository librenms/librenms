<?php

namespace LibreNMS\Polling\Method\Config;

final readonly class UnixAgentConfig extends PollingMethodConfig
{
    public function __construct(
        public int $port,
        public int $timeout,
    ) {
    }
}
