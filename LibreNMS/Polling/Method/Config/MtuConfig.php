<?php

namespace LibreNMS\Polling\Method\Config;

use LibreNMS\Enum\MtuFragOption;

final readonly class MtuConfig extends PollingMethodConfig
{
    public MtuFragOption $fragmentation;

    public function __construct(
        MtuFragOption|string $fragmentation,
        public string $ipVersion,
        public int $packetSize,
    ) {
        $this->fragmentation = is_string($fragmentation) ? MtuFragOption::from($fragmentation) : $fragmentation;
    }
}
