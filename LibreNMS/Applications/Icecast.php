<?php

namespace LibreNMS\Applications;

class Icecast extends ApplicationDefinition
{
    public const string TYPE = 'icecast';

    protected const array GRAPHS = [
        'cpuload' => 'CPU Load',
        'memoryusage' => 'Used Memory',
        'openfiles' => 'Open files',
    ];
}
