<?php

namespace LibreNMS\Applications;

class Freeswitch extends ApplicationDefinition
{
    public const string TYPE = 'freeswitch';
    protected const ?string DOCS = 'Freeswitch';

    protected const array GRAPHS = [
        'peak' => 'Freeswitch - Peak Calls',
        'calls' => 'Freeswitch - Calls',
        'channels' => 'Freeswitch - Channels',
        'callsIn' => 'Freeswitch - Inbound Calls',
        'callsOut' => 'Freeswitch - Outbound Calls',
    ];
}
