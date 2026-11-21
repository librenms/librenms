<?php

namespace LibreNMS\Applications;

class Redis extends ApplicationDefinition
{
    public const string TYPE = 'redis';
    protected const ?string DOCS = 'Redis';

    protected const ?array OVERVIEW = [
        'clients',
        'objects',
        'fragmentation',
        'usage',
        'defrag',
        'keyspace',
        'sync',
        'commands',
        'connections',
        'net',
    ];

    protected const array GRAPHS = [
        'clients' => 'Clients',
        'memory' => 'Memory',
        'commands' => 'Commands',
        'connections' => 'Connections',
        'defrag' => 'Defrag',
        'fragmentation' => 'Fragmentation',
        'keyspace' => 'Keyspace',
        'net' => 'Net',
        'objects' => 'Objects',
        'sync' => 'Sync',
        'usage' => 'Usage',
    ];
}
