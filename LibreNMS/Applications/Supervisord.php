<?php

namespace LibreNMS\Applications;

class Supervisord extends ApplicationDefinition
{
    public const string TYPE = 'supervisord';
    protected const ?string DOCS = 'Supervisord';

    protected const array GRAPHS = [
        'totals' => 'Totals',
        'uptime' => 'Process uptime',
    ];
}
