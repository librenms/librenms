<?php

namespace LibreNMS\Applications;

use App\Models\Application;

class Portactivity extends ApplicationDefinition
{
    public const string TYPE = 'portactivity';
    protected const ?string DOCS = 'Portactivity';

    protected const ?array OVERVIEW = ['ports'];

    protected const array GRAPHS = [
        'totals' => 'Total Connections',
        'total_details' => 'Total Connections Details',
        'to' => 'Connections To Server',
        'from' => 'Connections From Server',
    ];

    protected function buildDevicePage(ApplicationPage $page, Application $application): void
    {
        $ports = $application->data['ports'] ?? [];
        sort($ports);
        $port = $page->addResourceFilter('Ports', 'port', $ports);
        if ($port === null) {
            $page->notice = 'Please select a port.';

            return;
        }

        $page->addGraphs(self::GRAPHS, ['port' => $port]);
    }
}
