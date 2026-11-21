<?php

namespace LibreNMS\Applications;

use App\Models\Application;

class Mdadm extends ApplicationDefinition
{
    public const string TYPE = 'mdadm';
    protected const ?string DOCS = 'Mdadm';

    protected const array GRAPHS = [
        'level' => 'RAID level',
        'size' => 'RAID Size',
        'disc_count' => 'RAID Disk count',
        'hotspare_count' => 'RAID Hotspare Disk count',
        'degraded' => 'RAID degraded',
        'sync_speed' => 'RAID Sync speed',
        'sync_completed' => 'RAID Sync completed',
    ];

    protected function buildDevicePage(ApplicationPage $page, Application $application): void
    {
        $choices = $this->rrdResources($application);
        sort($choices);
        $selected = $page->addResourceFilter('RAID Arrays', 'array', $choices, 'All RAID Arrays');
        $page->addGraphs(self::GRAPHS, ['array' => $selected]);
    }
}
