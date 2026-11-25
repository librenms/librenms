<?php

namespace LibreNMS\Applications;

class NtpServer extends ApplicationDefinition
{
    public const string TYPE = 'ntp-server';
    protected const ?string DOCS = 'NTP Server';
    protected const ?string NAME = 'NTP Server';

    protected const ?array OVERVIEW = [
        'stats',
        'freq',
        'stratum',
        'buffer',
        'bits',
        'packets',
        'uptime',
    ];

    protected const array GRAPHS = [
        'stats' => 'NTPD Server - Statistics',
        'offset' => 'NTPD Server - Offset',
        'freq' => 'NTPD Server - Frequency',
        'stability' => 'NTPD Server - Stability',
        'stratum' => 'NTPD Server - Stratum',
        'buffer' => 'NTPD Server - Buffer',
        'bits' => 'NTPD Server - Packets Sent/Received',
        'packets' => 'NTPD Server - Packets Dropped/Ignored',
        'uptime' => 'NTPD Server - Uptime',
    ];
}
