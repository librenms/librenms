<?php

namespace LibreNMS\Applications;

use App\Models\Application;

class Pwrstatd extends ApplicationDefinition
{
    public const string TYPE = 'pwrstatd';
    protected const ?string DOCS = 'Pwrstatd';

    protected const ?array OVERVIEW = [
        'wattage',
        'voltage',
        'percentage',
        'minutes',
    ];

    protected const array GRAPHS = [
        'minutes' => 'Battery Runtime Remaining',
        'percentage' => 'Percentage Readings',
        'voltage' => 'Voltage Readings',
        'wattage' => 'Power Readings',
    ];

    protected function buildDevicePage(ApplicationPage $page, Application $application): void
    {
        $choices = $this->rrdResources($application);
        sort($choices);
        $selected = $page->addResourceFilter('PSUs', 'sn', $choices, 'All PSUs');
        $page->addGraphs(self::GRAPHS, ['sn' => $selected]);
    }
}
