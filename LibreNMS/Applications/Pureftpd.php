<?php

namespace LibreNMS\Applications;

class Pureftpd extends ApplicationDefinition
{
    public const string TYPE = 'pureftpd';
    protected const ?string DOCS = 'PureFTPd';
    protected const ?string NAME = 'PureFTPd';

    protected const array GRAPHS = [
        'bitrate' => 'PureFTPd - Bitrate',
        'connections' => 'PureFTPd - Connections',
        'users' => 'PureFTPd - Users Connected',
    ];
}
