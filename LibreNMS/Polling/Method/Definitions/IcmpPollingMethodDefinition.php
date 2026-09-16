<?php

namespace LibreNMS\Polling\Method\Definitions;

use LibreNMS\Polling\Method\Config\IcmpConfig;
use LibreNMS\Polling\Method\Probe\IcmpProbe;

/**
 * @extends PollingMethodDefinition<IcmpConfig>
 */
class IcmpPollingMethodDefinition extends PollingMethodDefinition
{
    public function icon(): string
    {
        return 'fa-exchange';
    }

    public function class(): string
    {
        return IcmpConfig::class;
    }

    public function probe(): IcmpProbe
    {
        return new IcmpProbe();
    }
}
