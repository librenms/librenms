<?php

namespace LibreNMS\Applications;

class NtpClient extends ApplicationDefinition
{
    public const string TYPE = 'ntp-client';
    protected const ?string NAME = 'NTP Client';
    protected const ?string GRAPH_PREFIX = 'ntp_client';

    protected const ?array OVERVIEW = [
        'stats',
        'freq',
    ];

    protected const array GRAPHS = [
        'stats' => 'NTP Client - Statistics',
        'offset' => 'NTP Client - Offset',
        'freq' => 'NTP Client - Frequency',
        'stability' => 'NTP Client - Stability',
    ];
}
