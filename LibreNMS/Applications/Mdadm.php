<?php

namespace LibreNMS\Applications;

use App\Models\Application;

class Mdadm extends ApplicationDefinition
{
    public const string TYPE = 'mdadm';

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
        $selected = $page->option('array');
        $page->addFilter('RAID Arrays', 'array', ['' => 'All RAID Arrays'] + array_combine($choices, $choices), $selected);
        $page->addGraphs(self::GRAPHS, ['array' => $selected]);
    }
}
