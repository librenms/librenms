<?php

namespace LibreNMS\Applications;

use App\Models\Application;

class Certificate extends ApplicationDefinition
{
    public const string TYPE = 'certificate';
    protected const ?string DOCS = 'Certificate';

    protected const array GRAPHS = [
        'age' => 'Age',
        'remaining_days' => 'Remaining days',
    ];

    protected function buildDevicePage(ApplicationPage $page, Application $application): void
    {
        $choices = $this->rrdResources($application);
        sort($choices);
        $selected = $page->addResourceFilter('Certificates', 'cert_name', $choices, 'All Certificates');
        $page->addGraphs(self::GRAPHS, ['cert_name' => $selected]);
    }
}
