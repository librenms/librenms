<?php

namespace LibreNMS\Applications;

class Backupninja extends ApplicationDefinition
{
    public const string TYPE = 'backupninja';
    protected const ?string DOCS = 'Backupninja';

    protected const array GRAPHS = [
        'backupninja' => 'backupninja backups',
    ];
}
