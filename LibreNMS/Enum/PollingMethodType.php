<?php

namespace LibreNMS\Enum;

use LibreNMS\Traits\HasUnknownCase;

enum PollingMethodType: string
{
    use HasUnknownCase;

    case Icmp = 'icmp';
    case Ipmi = 'ipmi';
    case Snmp = 'snmp';
    case UnixAgent = 'unix-agent';
    /** Stored types this version does not know, such as ones from a development branch */
    case Unknown = 'unknown';

    public function label(): string
    {
        return __('poller.methods.' . $this->value);
    }
}
