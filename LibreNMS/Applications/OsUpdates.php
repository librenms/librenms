<?php

namespace LibreNMS\Applications;

class OsUpdates extends ApplicationDefinition
{
    public const string TYPE = 'os-updates';
    protected const ?string DOCS = 'OS Updates';
    protected const ?string NAME = 'OS Updates';

    protected const array GRAPHS = [
        'packages' => 'OS updates',
    ];
}
