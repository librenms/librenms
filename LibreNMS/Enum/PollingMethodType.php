<?php

namespace LibreNMS\Enum;

use LibreNMS\Polling\Method\Config\PollingMethodConfig;
use LibreNMS\Polling\Method\Definitions\IcmpPollingMethodDefinition;
use LibreNMS\Polling\Method\Definitions\IpmiPollingMethodDefinition;
use LibreNMS\Polling\Method\Definitions\PollingMethodDefinition;
use LibreNMS\Polling\Method\Definitions\SnmpPollingMethodDefinition;
use LibreNMS\Polling\Method\Definitions\UnixAgentPollingMethodDefinition;

enum PollingMethodType: string
{
    case Icmp = 'icmp';
    case Ipmi = 'ipmi';
    case Snmp = 'snmp';
    case UnixAgent = 'unix-agent';

    /**
     * @return PollingMethodDefinition<PollingMethodConfig>
     */
    public function definition(): PollingMethodDefinition
    {
        return match ($this) {
            self::Icmp => app(IcmpPollingMethodDefinition::class),
            self::Ipmi => app(IpmiPollingMethodDefinition::class),
            self::Snmp => app(SnmpPollingMethodDefinition::class),
            self::UnixAgent => app(UnixAgentPollingMethodDefinition::class),
        };
    }

    public function hasSecret(): bool
    {
        return match ($this) {
            self::Ipmi, self::Snmp => true,
            self::Icmp, self::UnixAgent => false,
        };
    }
}
