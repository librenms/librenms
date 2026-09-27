<?php

namespace LibreNMS\Enum;

use LibreNMS\Polling\Method\Definitions\IcmpDefinition;
use LibreNMS\Polling\Method\Definitions\IpmiDefinition;
use LibreNMS\Polling\Method\Definitions\PollingMethodDefinition;
use LibreNMS\Polling\Method\Definitions\SnmpDefinition;
use LibreNMS\Polling\Method\Definitions\UnixAgentDefinition;
use LibreNMS\Polling\Method\Methods\IcmpPollingMethod;
use LibreNMS\Polling\Method\Methods\IpmiPollingMethod;
use LibreNMS\Polling\Method\Methods\PollingMethod;
use LibreNMS\Polling\Method\Methods\SnmpPollingMethod;
use LibreNMS\Polling\Method\Methods\UnixAgentPollingMethod;

enum PollingMethodType: string
{
    case Icmp = 'icmp';
    case Ipmi = 'ipmi';
    case Snmp = 'snmp';
    case UnixAgent = 'unix-agent';

    public function method(): PollingMethod
    {
        return resolve(match ($this) {
            self::Icmp => IcmpPollingMethod::class,
            self::Ipmi => IpmiPollingMethod::class,
            self::Snmp => SnmpPollingMethod::class,
            self::UnixAgent => UnixAgentPollingMethod::class,
        });
    }

    /**
     * The settings (form fields, validation rules) for this method.
     */
    public function definition(): PollingMethodDefinition
    {
        return match ($this) {
            self::Icmp => new IcmpDefinition,
            self::Ipmi => new IpmiDefinition,
            self::Snmp => new SnmpDefinition,
            self::UnixAgent => new UnixAgentDefinition,
        };
    }

    public function label(): string
    {
        return __('poller.methods.' . $this->value);
    }
}
