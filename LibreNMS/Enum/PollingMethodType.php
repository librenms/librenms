<?php

namespace LibreNMS\Enum;

enum PollingMethodType: string
{
    case Icmp = 'icmp';
    case Ipmi = 'ipmi';
    case Snmp = 'snmp';
    case UnixAgent = 'unix-agent';
}
