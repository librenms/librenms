<?php

namespace LibreNMS\Applications;

class Memcached extends ApplicationDefinition
{
    public const string TYPE = 'memcached';
    protected const ?string DOCS = 'Memcached';

    protected const ?array OVERVIEW = [
        'bits',
        'commands',
        'data',
        'items',
    ];

    protected const array GRAPHS = [
        'bits' => 'Traffic',
        'commands' => 'Commands',
        'data' => 'Data Size',
        'items' => 'Items',
        'hitmiss' => 'Hits/Misses',
        'uptime' => 'Uptime',
        'threads' => 'Threads',
    ];
}
