<?php

namespace LibreNMS\Applications;

class Opensips extends ApplicationDefinition
{
    public const string TYPE = 'opensips';

    protected const array GRAPHS = [
        'load' => 'Load Average',
        'memory' => 'Memory',
        'openfiles' => 'Open files',
    ];
}
