<?php

namespace LibreNMS\Applications;

class NfsV3Stats extends ApplicationDefinition
{
    public const string TYPE = 'nfs-v3-stats';
    protected const ?string NAME = 'NFS v3 Stats';

    protected const array GRAPHS = [
        'stats' => 'NFS v3 Statistics',
        'io' => 'IO',
        'fh' => 'File handler',
        'rc' => 'Reply cache',
        'ra' => 'Read ahead cache',
        'net' => 'Network stats',
        'rpc' => 'RPC Stats',
    ];
}
