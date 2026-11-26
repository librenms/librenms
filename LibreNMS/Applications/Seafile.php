<?php

namespace LibreNMS\Applications;

class Seafile extends ApplicationDefinition
{
    public const string TYPE = 'seafile';
    protected const ?string DOCS = 'Seafile';

    protected const array GRAPHS = [
        'connected' => 'Connected Devices',
        'enabled' => 'activated Accounts',
        'libraries' => 'Libraries',
        'trashed_libraries' => 'Trashed Libraries',
        'size_consumption' => 'Size Consumption',
        'groups' => 'Groups',
        'version' => 'Client Version',
        'platform' => 'Client Platform',
    ];
}
