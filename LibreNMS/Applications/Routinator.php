<?php

namespace LibreNMS\Applications;

use App\Models\Application;

class Routinator extends ApplicationDefinition
{
    public const string TYPE = 'routinator';
    protected const ?string DOCS = 'Routinator';

    protected const ?array OVERVIEW = [];

    protected const array GRAPHS = [
        'vrps' => 'Validated ROA Payloads (VRPs)',
        'update' => 'Validation run age & duration',
        'serial' => 'Serial number',
        'stale' => 'Stale objects',
        'rrdp' => 'RRDP repository status',
        'rsync' => 'rsync repository status',
        'repo_duration' => 'Repository fetch duration (max)',
        'rtr_connections' => 'RTR connections',
        'rtr_bytes' => 'RTR traffic',
        'tal_vrps' => 'VRPs per RIR',
        'tal_pubpoints' => 'Rejected publication points per RIR',
        'tal_invalid' => 'Invalid objects per RIR',
        'tal_manifests' => 'Missing manifests per RIR',
    ];

    protected const array CLIENT_GRAPHS = [
        'client_serial_lag' => 'RTR client serial lag',
        'client_update' => 'RTR client last update (seconds)',
        'client_bytes' => 'RTR client bytes written',
        'client_queries' => 'RTR client serial queries',
    ];

    protected function buildDevicePage(ApplicationPage $page, Application $application): void
    {
        $clients = $application->data['clients'] ?? [];
        sort($clients);
        $client = $page->addResourceFilter('RTR Clients', 'client', $clients, 'All RTR Clients');

        if ($client === null) {
            $page->addGraphs(self::GRAPHS);
        }
        if ($clients) {
            $page->addGraphs(self::CLIENT_GRAPHS, ['client' => $client]);
        }
    }
}
