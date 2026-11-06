<?php

namespace LibreNMS\Polling\Method\Config;

final readonly class MtuConfig extends PollingMethodConfig
{
    public function __construct(
        public string $ipVersion,
        public int $packetSize,
    ) {
    }
}
