<?php

namespace LibreNMS\Applications;

class LinuxConfigFiles extends ApplicationDefinition
{
    public const string TYPE = 'linux_config_files';
    protected const ?string DOCS = 'Linux config files';

    protected const array GRAPHS = [
        'number_of_confs' => 'Configuration Files Out-Of-Sync',
    ];
}
