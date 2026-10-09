<?php

namespace LibreNMS\Enum;

enum PollingMethodType: string
{
    case Icmp = 'icmp';
    case Ipmi = 'ipmi';
    case Snmp = 'snmp';
    case UnixAgent = 'unix-agent';

    public function label(): string
    {
        return __('poller.methods.' . $this->value);
    }
}
