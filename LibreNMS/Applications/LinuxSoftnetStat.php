<?php

namespace LibreNMS\Applications;

use App\Models\Application;

class LinuxSoftnetStat extends ApplicationDefinition
{
    public const string TYPE = 'linux_softnet_stat';
    protected const ?string DOCS = 'Linux Softnet Stat';

    protected const array GRAPHS = [
        'packets' => 'Packets Per Second',
        'time_squeeze' => 'Time Squeezes Per Second',
        'backlog_length' => 'Backlog Length',
        'packet_dropped' => 'Packets Dropped Per Second',
        'cpu_collision' => 'CPU Collisions Per Second',
        'flow_limit' => 'Flow Limit Hit Per Second',
        'received_rps' => 'Received RPS Per Second',
        'budget' => 'Budget Sysctl Values',
    ];

    protected function buildDevicePage(ApplicationPage $page, Application $application): void
    {
        $page->details['Current Budget'] = $application->data['budget'] ?? 'unknown';
        $page->details['Current Budget usecs'] = $application->data['budget_usecs'] ?? 'unknown';
        parent::buildDevicePage($page, $application);
    }
}
