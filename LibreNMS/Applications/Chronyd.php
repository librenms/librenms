<?php

namespace LibreNMS\Applications;

use App\Models\Application;

class Chronyd extends ApplicationDefinition
{
    public const string TYPE = 'chronyd';
    protected const ?string DOCS = 'Chronyd';

    protected const ?array OVERVIEW = [
        'time',
        'frequency',
        'root',
    ];

    protected const array GRAPHS = [
        'time' => 'System time',
        'frequency' => 'System clock frequency',
        'root' => 'Root stratum',
        'stratum' => 'Stratum level',
    ];

    protected const array SOURCE_GRAPHS = [
        'source_sampling' => 'Clock sampling offsets',
        'source_frequency' => 'Clock residual frequency',
        'source_polling' => 'Polling',
    ];

    protected function buildDevicePage(ApplicationPage $page, Application $application): void
    {
        $sources = $application->data['sources'] ?? [];
        sort($sources);
        $source = $page->addResourceFilter('Sources', 'source', $sources, 'Tracking');
        $page->addGraphs($source === null ? self::GRAPHS : self::SOURCE_GRAPHS, ['source' => $source]);
    }
}
