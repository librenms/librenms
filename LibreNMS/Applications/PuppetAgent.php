<?php

namespace LibreNMS\Applications;

class PuppetAgent extends ApplicationDefinition
{
    public const string TYPE = 'puppet-agent';
    protected const ?string DOCS = 'Puppet Agent';

    protected const array GRAPHS = [
        'last_run' => 'Last run',
        'changes' => 'Changes',
        'events' => 'Events',
        'resources' => 'Resources',
        'time' => 'Runtime',
    ];
}
