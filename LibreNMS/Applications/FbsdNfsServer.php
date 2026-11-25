<?php

namespace LibreNMS\Applications;

class FbsdNfsServer extends ApplicationDefinition
{
    public const string TYPE = 'fbsd-nfs-server';
    protected const ?string DOCS = 'NFS FreeBSD Server';
    protected const ?string NAME = 'FreeBSD NFS Server';

    protected const array GRAPHS = [
        'stats' => 'Stats',
        'cache' => 'Cache',
        'gathering' => 'Write Gathering',
    ];
}
