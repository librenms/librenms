<?php

namespace LibreNMS\Enum;

use LibreNMS\Interfaces\PollingMethodConfigInterface;
use LibreNMS\Interfaces\PollingMethodDefinitionInterface;

enum PollingMethodType: string
{
    case Icmp = 'icmp';
    case Ipmi = 'ipmi';
    case Snmp = 'snmp';
    case UnixAgent = 'unix-agent';

    /**
     * @return PollingMethodDefinitionInterface<PollingMethodConfigInterface>
     */
    public function definition(): PollingMethodDefinitionInterface
    {
        return app("polling.method.$this->value");
    }

    public function hasSecret(): bool
    {
        return $this->definition()->secretDefinition() !== null;
    }
}
