<?php

namespace LibreNMS\Enum;

enum AvailabilitySource: string
{
    case NONE = '';
    case SNMP = 'snmp';
    case ICMP = 'icmp';
}
