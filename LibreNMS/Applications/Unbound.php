<?php

namespace LibreNMS\Applications;

class Unbound extends ApplicationDefinition
{
    public const string TYPE = 'unbound';
    protected const ?string DOCS = 'Unbound';

    protected const ?array OVERVIEW = [
        'queries',
        'cache',
        'operations',
    ];

    protected const array GRAPHS = [
        'queries' => 'Unbound - Queries',
        'cache' => 'Unbound - Cache',
        'operations' => 'Unbound - Operations',
        'requestlist' => 'Unbound - Request list',
        'recursiontime' => 'Unbound - Recursion time',
    ];
}
