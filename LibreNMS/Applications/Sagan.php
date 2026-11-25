<?php

namespace LibreNMS\Applications;

use App\Models\Application;

class Sagan extends ApplicationDefinition
{
    public const string TYPE = 'sagan';
    protected const ?string DOCS = 'Sagan';

    protected const ?array OVERVIEW = [
        'after',
        'bytes_ignored',
        'bytes',
        'drop_percent',
        'drop',
        'eps',
        'f_drop_percent',
        'f_dropped',
        'f_total',
        'ignore',
        'match',
        'max_bytes_log_line',
        'threshold',
        'total',
        'uptime',
        'alert',
    ];

    protected const array GRAPHS = [
        'bytes' => 'Bytes',
        'eps' => 'Events Per Second',
        'total' => 'Recieved Log Entries',
        'drop' => 'Drop',
        'drop_percent' => 'Drop Percent',
        'f_total' => 'Flows Total',
        'f_dropped' => 'Flows Dropped',
        'f_drop_percent' => 'Flows Dropped Percent',
        'ignore' => 'Ignore',
        'bytes_ignored' => 'Bytes Ignored',
        'match' => 'Match',
        'max_bytes_log_line' => 'Max Bytes Log Line',
        'threshold' => 'Threshold',
        'uptime' => 'Uptime',
        'alert' => 'Alert: 0=OK, 1=WARNING, 2=CRITICAL, 3+UNKNOWN',
    ];

    protected function buildDevicePage(ApplicationPage $page, Application $application): void
    {
        $choices = $application->data['instances'] ?? [];
        sort($choices);
        $selected = $page->addResourceFilter('Instances', 'sinstance', $choices, 'Totals');
        $page->addGraphs(self::GRAPHS, ['sinstance' => $selected]);
    }
}
