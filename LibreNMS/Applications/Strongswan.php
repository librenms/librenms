<?php

namespace LibreNMS\Applications;

use App\Models\Application;

class Strongswan extends ApplicationDefinition
{
    public const string TYPE = 'strongswan';
    protected const ?string DOCS = 'Strongswan';

    protected const array TUNNEL_GRAPHS = [
        'bits_in' => 'Inbound traffic (bits/s)',
        'bits_out' => 'Outbound traffic (bits/s)',
        'packets_in' => 'Inbound packets/s',
        'packets_out' => 'Outbound packets/s',
        'state' => 'Tunnel state (1=established)',
        'children' => 'Installed child SAs',
        'reestablishes' => 'Re-establishments/s',
    ];

    protected const array GLOBAL_GRAPHS = [
        'global_rekeys' => 'Global rekeys/s',
        'global_errors' => 'Global errors/s (invalid / invalid SPI)',
    ];

    protected function buildDevicePage(ApplicationPage $page, Application $application): void
    {
        $tunnels = $this->rrdResources($application);
        $labels = $application->data['labels'] ?? [];
        $choices = ['' => 'All tunnels'];
        foreach ($tunnels as $tunnel) {
            if (str_starts_with($tunnel, 'tun_')) {
                $choices[$tunnel] = $labels[$tunnel] ?? substr($tunnel, 4);
            }
        }
        $tunnel = $page->option('tunnel');
        $page->addFilter('Tunnels', 'tunnel', $choices, $tunnel);
        $label = $choices[$tunnel ?? ''] ?? substr($tunnel, 4);
        $page->addGraphs(array_map(fn (string $title) => "$title — $label", self::TUNNEL_GRAPHS), ['tunnel' => $tunnel]);
        $page->addGraphs(self::GLOBAL_GRAPHS);
    }
}
