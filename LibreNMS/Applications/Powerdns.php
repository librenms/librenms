<?php

namespace LibreNMS\Applications;

class Powerdns extends ApplicationDefinition
{
    public const string TYPE = 'powerdns';
    protected const ?string DOCS = 'PowerDNS';
    protected const ?string NAME = 'PowerDNS';

    protected const array GRAPHS = [
        'latency' => 'PowerDNS - Latency',
        'fail' => 'PowerDNS - Corrupt / Failed / Timed out',
        'packetcache' => 'PowerDNS - Packet Cache',
        'querycache' => 'PowerDNS - Query Cache',
        'recursing' => 'PowerDNS - Recursing Queries and Answers',
        'queries' => 'PowerDNS - Total UDP/TCP Queries and Answers',
        'queries_udp' => 'PowerDNS - Detail UDP IPv4/IPv6 Queries and Answers',
    ];
}
