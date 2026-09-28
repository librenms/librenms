<?php

namespace LibreNMS\Polling\Method\Config;

final readonly class IcmpConfig extends PollingMethodConfig
{
    public function __construct(
        public string $ipVersion,
    ) {
    }
}
