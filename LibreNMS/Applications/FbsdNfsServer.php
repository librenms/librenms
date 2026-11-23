<?php

namespace LibreNMS\Applications;

class FbsdNfsServer extends ApplicationDefinition
{
    public const string TYPE = 'fbsd-nfs-server';
    protected const ?string NAME = 'FreeBSD NFS Server';
    protected const ?string GRAPH_PREFIX = 'fbsd_nfs_server';

    protected const array GRAPHS = [
        'stats' => 'Stats',
        'cache' => 'Cache',
        'gathering' => 'Write Gathering',
    ];
}
