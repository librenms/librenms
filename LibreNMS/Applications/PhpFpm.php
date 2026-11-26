<?php

namespace LibreNMS\Applications;

use App\Models\Application;

class PhpFpm extends ApplicationDefinition
{
    public const string TYPE = 'php-fpm';
    protected const ?string DOCS = 'PHP-FPM';
    protected const ?string NAME = 'PHP-FPM';

    protected const ?array OVERVIEW = [
        'overview_combined',
        'overview_slow_requests',
        'overview_max_children_reached',
        'v1_last_request_cpu',
    ];

    protected const array GRAPHS = [
        'v1_combined' => 'Combined',
        'v1_accepted_conn' => 'Connections Per Second',
        'v1_last_request_cpu' => 'Last Request CPU',
        'v1_slow_requests' => 'Slow Requests',
        'v1_active_processes' => 'Active Procs',
        'v1_idle_processes' => 'Idle Procs',
        'v1_total_processes' => 'Total Procs',
        'v1_max_active_processes' => 'Max Active Procs',
        'v1_listen_queue' => 'Listen Queue',
        'v1_max_listen_queue' => 'Max Listen Queue',
        'v1_listen_queue_len' => 'Listen Queue Len',
        'v1_max_children_reached' => 'Max Children Reached',
        'v1_start_since' => 'Uptime',
    ];

    protected function buildDevicePage(ApplicationPage $page, Application $application): void
    {
        if (($application->data['version'] ?? null) === 'legacy') {
            $page->addGraphs(['stats' => 'PHP-FPM']);

            return;
        }

        $pools = $application->data['pools'] ?? [];
        sort($pools);
        $pool = $page->addResourceFilter('Pools', 'phpfpm_pool', $pools, 'Totals');
        $page->addGraphs(self::GRAPHS, ['phpfpm_pool' => $pool]);
    }
}
