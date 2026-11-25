<?php

namespace LibreNMS\Applications;

use App\Facades\PortCache;
use App\Models\Application;
use App\Models\Device;
use Illuminate\Contracts\Support\Htmlable;

class Wireguard extends ApplicationDefinition
{
    public const string TYPE = 'wireguard';
    protected const ?string DOCS = 'Wireguard';

    protected const ?array OVERVIEW = ['traffic', 'time'];

    protected function buildDevicePage(ApplicationPage $page, Application $application): void
    {
        $interfaces = $application->data['mappings'] ?? [];
        $peers = $application->data['data'] ?? [];
        ksort($interfaces);
        $interface = $page->option('interface');
        $client = $page->option('client');
        $section = $page->option('wg_page');

        $views = ['' => 'All Interfaces'];
        if ($peers) {
            $views['details'] = 'Details';
        }
        $page->addFilter('WireGuard', 'wg_page', $views, $interface === null ? $section : false);
        $names = array_keys($interfaces);
        $page->addFilter('Interfaces', 'interface', array_combine($names, $names), $interface);

        if ($section === 'details') {
            $this->detailsTable($page, $application, $peers);

            return;
        }
        if ($interface === null) {
            $page->addGraphs(['traffic' => 'Total Wireguard Traffic']);

            return;
        }

        $clients = $interfaces[$interface] ?? [];
        sort($clients);
        $context = ['interface' => $interface];
        $page->addFilter('Graphs', 'wg_page', [
            '' => 'Interface Traffic',
            'peer_bw' => 'Peer Traffic',
            'peer_last' => 'Last Handshake',
        ], $client === null ? $section : false, $context);
        $page->addFilter('Peers', 'client', array_combine($clients, $clients), $client, $context);

        if ($client !== null) {
            if (isset($peers[$interface][$client])) {
                $this->peerDetails($page, $application, $interface, $peers[$interface][$client]);
            }
            $page->addGraphs([
                'traffic' => "Peer Traffic, $interface - $client",
                'time' => "Peer Minutes Since Last Handshake, $interface - $client",
            ], $context + ['client' => $client]);

            return;
        }

        if ($section !== 'peer_last') {
            $page->addGraphs(['traffic' => "Total Wireguard Traffic, $interface"], $context);
        }
        if (in_array($section, ['peer_bw', 'peer_last'], true)) {
            foreach ($clients as $name) {
                $graphs = $section === 'peer_bw'
                    ? ['traffic' => "Peer Traffic, $interface - $name"]
                    : ['time' => "Peer Minutes Since Last Handshake, $interface - $name"];
                $page->addGraphs($graphs, $context + ['client' => $name]);
            }
        }
    }

    /** @param  array<string, mixed>  $peer */
    private function peerDetails(ApplicationPage $page, Application $application, string $interface, array $peer): void
    {
        $hostname = $peer['hostname'] ?? '*unknown*';
        $page->details = [
            'Hostname' => $this->deviceReference($hostname, $peer['hostname'] ?? null),
            'PubKey' => $peer['pubkey'] ?? '*hidden*',
            'Interface' => $this->interfaceReference($application, $interface),
            'Endpoint Host' => $this->addressReference($peer['endpoint_host'] ?? ''),
            'Endpoint Port' => $peer['endpoint_port'] ?? '',
            'Minutes Since Last Handshake' => $peer['minutes_since_last_handshake'] ?? '',
            'Allowed IPs' => $this->addresses($peer['allowed_ips'] ?? []),
        ];
    }

    /** @param  array<string, array<string, array<string, mixed>>>  $interfaces  peers keyed by interface and peer name */
    private function detailsTable(ApplicationPage $page, Application $application, array $interfaces): void
    {
        ksort($interfaces);
        $rows = [];
        foreach ($interfaces as $interface => $peers) {
            ksort($peers);
            $interfaceLink = $this->interfaceReference($application, $interface);
            foreach ($peers as $name => $peer) {
                $rows[] = [
                    $this->deviceReference($name, $peer['hostname'] ?? null),
                    $interfaceLink,
                    view('apps.peer-link', ['label' => $peer['pubkey'] ?? '*hidden*', 'url' => $application->deviceAppsUrl(['interface' => $interface, 'client' => $name])]),
                    $peer['bytes_rcvd'] ?? '',
                    $peer['bytes_sent'] ?? '',
                    $this->addressReference($peer['endpoint_host'] ?? ''),
                    $peer['endpoint_port'] ?? '',
                    isset($peer['minutes_since_last_handshake']) ? sprintf('%01.2f', $peer['minutes_since_last_handshake']) : '',
                    $this->addresses($peer['allowed_ips'] ?? []),
                ];
            }
        }

        $page->addTable('Peers', ['Name', 'Interface', 'PubKey', 'Recv', 'Sent', 'Endpoint IP', 'Port', 'Last Handshake', 'Allowed IPs'], $rows);
    }

    /** @param  list<string>  $addresses */
    private function addresses(array $addresses): Htmlable
    {
        return view('apps.address-list', ['addresses' => array_map($this->addressReference(...), $addresses)]);
    }

    private function addressReference(string $address): Htmlable
    {
        return view('apps.port-reference', [
            'label' => $address,
            'port' => $address === '' ? null : PortCache::getByIp($address),
        ]);
    }

    private function interfaceReference(Application $application, string $interface): Htmlable
    {
        return view('apps.port-name', [
            'name' => $interface,
            'port' => PortCache::getByIfName($interface, $application->device_id),
        ]);
    }

    private function deviceReference(string $label, ?string $hostname): Htmlable
    {
        return view('apps.device-reference', [
            'label' => $label,
            'device' => $hostname === null ? null : Device::firstWhere('hostname', $hostname),
        ]);
    }
}
