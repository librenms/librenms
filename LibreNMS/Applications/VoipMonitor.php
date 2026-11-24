<?php

namespace LibreNMS\Applications;

class VoipMonitor extends ApplicationDefinition
{
    public const string TYPE = 'voip-monitor';

    protected const array GRAPHS = [
        'cpuload' => 'CPU Load',
        'memoryusage' => 'Used Memory',
        'openfiles' => 'Open files',
    ];
}
