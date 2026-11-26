<?php

namespace LibreNMS\Applications;

use App\Models\Application;
use App\Models\Port;

class DhcpStats extends ApplicationDefinition
{
    public const string TYPE = 'dhcp-stats';
    protected const ?string DOCS = 'ISC DHCP Stats';
    protected const ?string NAME = 'DHCP Stats';

    protected const array GRAPHS = [
        'stats' => 'Stats',
        'pools_percent' => 'Pools Percent',
        'pools_current' => 'Pools Current',
        'pools_max' => 'Pools Max',
        'networks_percent' => 'Networks Percent',
        'networks_current' => 'Networks Current',
        'networks_max' => 'Networks Max',
    ];

    protected function buildDevicePage(ApplicationPage $page, Application $application): void
    {
        $data = $application->data ?? [];
        $section = $page->option('app_page');
        if (isset($data['pools'])) {
            $page->addFilter('DHCP Stats', 'app_page', ['' => 'General', 'pools' => 'Pools', 'leases' => 'Leases'], $section);
            if ($section === 'pools') {
                $this->pools($page, $data['pools'], $data['networks'] ?? []);

                return;
            }
            if ($section === 'leases') {
                $this->leases($page, $data['found_leases'] ?? []);

                return;
            }
        }

        parent::buildDevicePage($page, $application);
    }

    /**
     * @param  list<array<string, mixed>>  $pools
     * @param  list<array<string, mixed>>  $networks
     */
    private function pools(ApplicationPage $page, array $pools, array $networks): void
    {
        $rows = array_map(fn (array $pool) => [
            $pool['cidr'], $pool['first_ip'], $pool['last_ip'], $pool['max'], $pool['cur'], $pool['percent'],
        ], $pools);
        $page->addTable('Pools', ['CIDR', 'First IP', 'Last IP', 'Max', 'In Use', 'Use%'], $rows);

        foreach ($pools as $pool) {
            $title = $pool['cidr'] . ', ' . $pool['first_ip'] . '-' . $pool['last_ip'];
            unset($pool['cur'], $pool['max'], $pool['percent']);
            $rows = [];
            foreach ($pool as $key => $value) {
                $rows[] = [$key, is_array($value) ? json_encode($value, JSON_INVALID_UTF8_SUBSTITUTE) : $value];
            }
            $page->addTable($title, ['Key', 'Value'], $rows);
        }

        $rows = array_map(fn (array $network) => [
            $network['network'], $network['max'], $network['cur'], $network['percent'], json_encode($network['pools']),
        ], $networks);
        $page->addTable('Networks', ['Name', 'Max', 'In Use', 'Use%', 'Pools'], $rows);
    }

    /** @param  list<array<string, mixed>>  $leases */
    private function leases(ApplicationPage $page, array $leases): void
    {
        $macs = [];
        foreach ($leases as $lease) {
            $mac = (string) ($lease['hw_address'] ?? '');
            if (preg_match('/^(?:[a-f\d]{2}:){5}[a-f\d]{2}$/iD', $mac)) {
                $macs[] = strtolower(str_replace(':', '', $mac));
            }
        }
        $ports = $macs === [] ? collect() : Port::with('device')->whereIn('ifPhysAddress', array_unique($macs))->orderBy('port_id')->get()
            ->groupBy(fn (Port $port) => strtolower(str_replace(':', '', (string) $port->ifPhysAddress)))->map->first();

        $rows = [];
        foreach ($leases as $lease) {
            $mac = (string) ($lease['hw_address'] ?? '');
            $rows[] = [
                $lease['ip'],
                $lease['state'],
                view('apps.port-reference', ['label' => $mac, 'port' => $ports->get(strtolower(str_replace(':', '', $mac)))]),
                gmdate('Y-m-d\TH:i:s\Z', $lease['starts']),
                gmdate('Y-m-d\TH:i:s\Z', $lease['ends']),
                base64_decode((string) ($lease['client_hostname'] ?? '')),
                base64_decode((string) ($lease['vendor_class_identifier'] ?? '')),
            ];
        }
        $page->addTable('Leases', ['IP', 'State', 'HW Address', 'Starts', 'Ends', 'Client Hostname', 'Vendor'], $rows);
    }
}
