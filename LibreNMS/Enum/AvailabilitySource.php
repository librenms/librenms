<?php

namespace LibreNMS\Enum;

enum AvailabilitySource: string
{
    case None = '';
    case Snmp = 'snmp';
    case Icmp = 'icmp';
    case Both = 'icmp,snmp';
}
