<?php

namespace LibreNMS\Applications;

class Drbd extends ApplicationDefinition
{
    public const string TYPE = 'drbd';

    protected const ?array OVERVIEW = [
        'disk_bits',
        'network_bits',
        'queue',
        'unsynced',
    ];

    protected const array GRAPHS = [
        'network_bits' => 'Network Traffic',
        'disk_bits' => 'Disk Traffic',
        'unsynced' => 'Unsynced Data',
        'queue' => 'Queues',
    ];
}
