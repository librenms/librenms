<?php

namespace LibreNMS\Applications;

class Asterisk extends ApplicationDefinition
{
    public const string TYPE = 'asterisk';

    protected const array GRAPHS = [
        'calls' => 'Asterisk - Calls',
        'channels' => 'Asterisk - Channels',
        'sip' => 'Asterisk - SIP Peers',
        'iax2' => 'Asterisk - IAX2 Peers',
    ];
}
