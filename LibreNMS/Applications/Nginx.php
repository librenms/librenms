<?php

namespace LibreNMS\Applications;

class Nginx extends ApplicationDefinition
{
    public const string TYPE = 'nginx';

    protected const array GRAPHS = [
        'connections' => 'nginx Connections',
        'req' => 'nginx requests',
    ];
}
