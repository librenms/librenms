<?php

namespace LibreNMS\Applications;

use App\Models\Application;

class Docker extends ApplicationDefinition
{
    public const string TYPE = 'docker';
    protected const ?string DOCS = 'Docker Stats';

    protected const ?array OVERVIEW = [
        'cpu_usage',
        'pids',
        'mem_limit',
        'mem_used',
        'mem_perc',
    ];

    protected const array GRAPHS = [
        'pids' => 'PIDs',
        'mem_limit' => 'Container memory limit',
        'mem_used' => 'Container memory used',
        'cpu_usage' => 'Container CPU usage, %',
        'mem_perc' => 'Container Memory usage, %',
        'uptime' => 'Container uptime',
        'size_rw' => 'Container Size RW',
        'size_root_fs' => 'Container Size Root FS',
    ];

    protected function buildDevicePage(ApplicationPage $page, Application $application): void
    {
        $containers = $application->data['containers'] ?? [];
        sort($containers);
        $container = $page->addResourceFilter('Containers', 'container', $containers, 'All Containers');

        if ($container === null) {
            $page->addGraphs(['totals' => 'Totals status']);
        }
        $page->addGraphs(self::GRAPHS, ['container' => $container]);
    }
}
