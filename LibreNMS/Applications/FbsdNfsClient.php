<?php

namespace LibreNMS\Applications;

class FbsdNfsClient extends ApplicationDefinition
{
    public const string TYPE = 'fbsd-nfs-client';
    protected const ?string NAME = 'FreeBSD NFS Client';
    protected const ?string GRAPH_PREFIX = 'fbsd_nfs_client';

    protected const array GRAPHS = [
        'stats' => 'Stats',
        'cache' => 'Cache',
        'rpc' => 'RPC',
    ];
}
