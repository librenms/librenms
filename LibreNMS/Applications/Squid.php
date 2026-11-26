<?php

namespace LibreNMS\Applications;

class Squid extends ApplicationDefinition
{
    public const string TYPE = 'squid';
    protected const ?string DOCS = 'Squid';

    protected const ?array OVERVIEW = [
        'memory',
        'clients',
        'cpuusage',
        'objcount',
        'filedescr',
        'httpbw',
        'http',
        'server',
        'serverbw',
        'reqhit',
        'bytehit',
        'sysnumread',
        'pagefaults',
        'cputime',
    ];

    protected const array GRAPHS = [
        'bytehit' => 'Byte Hits',
        'reqhit' => 'Request Hits',
        'http' => 'Client HTTP',
        'httpbw' => 'Client HTTP Bandwidth',
        'server' => 'Server HTTP',
        'serverbw' => 'Server HTTP Bandwidth',
        'clients' => 'Clients',
        'cputime' => 'CPU Time',
        'cpuusage' => 'CPU Usage',
        'filedescr' => 'File Descriptors',
        'memory' => 'Memory',
        'objcount' => 'Object Count',
        'pagefaults' => 'Pagefaults',
        'sysnumread' => 'Sys Read',
    ];
}
