<?php

namespace LibreNMS\Applications;

class PuppetAgent extends ApplicationDefinition
{
    public const string TYPE = 'puppet-agent';
    protected const ?string GRAPH_PREFIX = 'puppet_agent';

    protected const array GRAPHS = [
        'last_run' => 'Last run',
        'changes' => 'Changes',
        'events' => 'Events',
        'resources' => 'Resources',
        'time' => 'Runtime',
    ];
}
