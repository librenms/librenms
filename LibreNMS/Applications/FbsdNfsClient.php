<?php

namespace LibreNMS\Applications;

class FbsdNfsClient extends ApplicationDefinition
{
    public const string TYPE = 'fbsd-nfs-client';
    protected const ?string DOCS = 'NFS FreeBSD Client';
    protected const ?string NAME = 'FreeBSD NFS Client';

    protected const array GRAPHS = [
        'stats' => 'Stats',
        'cache' => 'Cache',
        'rpc' => 'RPC',
    ];
}
