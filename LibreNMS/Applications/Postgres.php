<?php

namespace LibreNMS\Applications;

use App\Models\Application;

class Postgres extends ApplicationDefinition
{
    public const string TYPE = 'postgres';
    protected const ?string DOCS = 'Postgres';

    protected const array GRAPHS = [
        'backends' => 'Backends',
        'cr' => 'Commits & Rollbacks',
        'rows' => 'Rows',
        'hr' => 'Buffer Hits & Disk Blocks Read',
        'index' => 'Indexes',
        'sequential' => 'Sequential',
    ];

    protected function buildDevicePage(ApplicationPage $page, Application $application): void
    {
        $choices = $application->data['databases'] ?? [];
        sort($choices);
        $selected = $page->addResourceFilter('Databases', 'database', $choices, 'Total');
        $page->addGraphs(self::GRAPHS, ['database' => $selected]);
    }
}
