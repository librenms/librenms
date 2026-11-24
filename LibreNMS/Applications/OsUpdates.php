<?php

namespace LibreNMS\Applications;

class OsUpdates extends ApplicationDefinition
{
    public const string TYPE = 'os-updates';
    protected const ?string NAME = 'OS Updates';
    protected const ?string GRAPH_PREFIX = 'os_updates';

    protected const array GRAPHS = [
        'packages' => 'OS updates',
    ];
}
