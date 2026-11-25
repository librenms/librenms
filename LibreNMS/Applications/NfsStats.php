<?php

namespace LibreNMS\Applications;

class NfsStats extends ApplicationDefinition
{
    public const string TYPE = 'nfs-stats';
    protected const ?string NAME = 'NFS Stats';

    protected const ?array OVERVIEW = [];

    protected const array GRAPHS = [
        '' => 'NFS Server - Statistics',
    ];
}
