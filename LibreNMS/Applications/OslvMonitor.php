<?php

namespace LibreNMS\Applications;

use App\Facades\LibrenmsConfig;
use App\Facades\PortCache;
use App\Models\Application;
use App\Models\Device;
use App\Models\Port;

class OslvMonitor extends ApplicationDefinition
{
    public const string TYPE = 'oslv_monitor';
    protected const ?string DOCS = 'OS Level Virtualization Monitoring';
    protected const ?string NAME = 'OS Level Virtualization';

    protected const ?array OVERVIEW = [
        'cpu_percent', 'mem_percent', 'time', 'procs', 'sizes', 'rss', 'vsz', 'faults', 'rwd_amount', 'ops_rwd', 'cows',
        'sock', 'recv_sent_msgs', 'etimes', 'swaps', 'signals_taken', 'switches',
    ];

    protected const array GRAPHS = [
        'cpu_percent' => 'CPU Usage Percent',
        'mem_percent' => 'Memory Usage Percent',
        'time' => 'CPU/System/User Time in secs/sec',
        'procs' => 'Processes',
        'sizes' => 'Size, Data, Text in kbytes',
        'rss' => 'Real Memory (Resident Set Size) in kbytes',
        'vsz' => 'Virtual Size in kbytes',
        'faults' => 'Minor/Major Faults Per Second',
        'switches' => 'Context Switches Per Second',
        'etimes' => 'Elapsed Time',
    ];

    private const array METRIC_GRAPHS = [
        'rwdblocks' => ['blocks' => 'Read/Write Blocks Per Second'],
        'rwdops' => ['ops_rwd' => 'Read/Write Ops Per Second'],
        'rwdbytes' => ['bytes_rwd' => 'Read/Write Bytes Per Second'],
        'signals-taken' => ['signals_taken' => 'Signals Taken Per Second'],
        'recv_sent_msgs' => ['recv_sent_msgs' => 'Messages Received/Sent Per Second'],
        'cows' => ['cows' => 'COWs Per Second'],
        'swaps' => ['swaps' => 'Swaps Per Second'],
        'sock' => ['sock' => 'Socket Buffer Size In Bytes'],
        'throttled_time' => ['throttled_time' => 'CPU Throttled Time Seconds Per Second'],
        'throttled_count' => ['throttled_count' => 'CPU Throttled Events Per Second'],
        'burst_time' => ['burst_time' => 'CPU Burst Time Seconds Per Second'],
        'burst_count' => ['burst_count' => 'CPU Burst Events Per Second'],
    ];

    private const array LINUX_MEMORY_GRAPHS = [
        'linux_pg_memory_stats' => ['cgroups_pg' => 'Linux Pg Memory Stats'],
        'misc_linux_memory_stats' => ['cgroups_mem_misc' => 'Misc Linux Memory Stats'],
        'zswap_size' => ['cgroups_zswap' => 'Zswap Size'],
        'zswap_activity' => ['cgroups_zswap_activity' => 'Zswap Activity'],
        'workingset_stats' => ['cgroups_workingset' => 'Workingset Stats'],
        'thp_activity' => ['cgroups_thp_activity' => 'THP Activity'],
    ];

    protected function buildDevicePage(ApplicationPage $page, Application $application): void
    {
        $data = $application->data ?? [];
        $selected = $page->option('oslvm');
        $this->resourceFilters($page, $data, $selected);

        $info = $data['oslvm_data'][$selected ?? ''] ?? [];
        if (! empty($info['path'])) {
            $this->mountPoints($page, $application->device, $info['path']);
        }
        if (! empty($info['ip'])) {
            $this->network($page, $application->device, $info['ip']);
        }

        $graphs = self::GRAPHS;
        $has = $data['has'] ?? [];
        foreach (self::METRIC_GRAPHS as $metric => $items) {
            if (! empty($has[$metric])) {
                $graphs += $items;
            }
        }
        if (! empty($has['linux_mem_stats'])) {
            foreach (self::LINUX_MEMORY_GRAPHS as $setting => $items) {
                if (LibrenmsConfig::get('apps.oslv_monitor.' . $setting)) {
                    $graphs += $items;
                }
            }
        }
        $page->addGraphs($graphs, ['oslvm' => $selected]);
    }

    /** @param  array<string, mixed>  $data */
    private function resourceFilters(ApplicationPage $page, array $data, ?string $selected): void
    {
        $backend = $data['backend'] ?? null;
        $current = $data['oslvms'] ?? [];
        $previous = $data['inactive'] ?? [];
        sort($previous);

        if ($backend !== 'cgroups') {
            $label = match ($backend) {
                'FreeBSD' => 'Jails',
                null => 'Instances',
                default => 'OSLVMs',
            };
            $page->addFilter($label, 'oslvm', ['' => 'Totals'] + array_combine($current, $current), $selected);
            $page->addFilter('Old ' . $label, 'oslvm', array_combine($previous, $previous), $selected);

            return;
        }

        $page->addFilter('Containers', 'oslvm', ['' => 'Totals'], $selected);
        $groups = [
            'p' => 'Podman Containers',
            'd' => 'Docker Containers',
            's' => 'SystemD Containers',
            'u' => 'User Containers',
            'other' => 'Other Containers',
        ];
        $current = $this->containerGroups($current, $data['uid_mapping'] ?? []);
        $previous = $this->containerGroups($previous, $data['uid_mapping'] ?? []);
        foreach ($groups as $key => $label) {
            $old = $previous[$key] ?? [];
            $page->addFilter(($old ? 'Current ' : '') . $label, 'oslvm', $current[$key] ?? [], $selected);
            $page->addFilter('Previous ' . $label, 'oslvm', $old, $selected);
        }
    }

    /**
     * @param  list<string>  $containers
     * @param  array<string, array<string, mixed>>  $users
     * @return array<string, array<string, string>> container labels grouped by type
     */
    private function containerGroups(array $containers, array $users): array
    {
        $groups = [];
        foreach ($containers as $container) {
            $prefix = substr($container, 0, 2);
            if (in_array($prefix, ['p_', 'd_', 's_', 'u_'], true)) {
                $key = $prefix[0];
                $label = substr($container, 2);
            } else {
                $key = str_starts_with($container, 'libpod') ? 'p' : 'other';
                $label = $container;
            }
            if ($key === 'u' && isset($users[$label]['name'])) {
                $label .= ' (' . $users[$label]['name'] . ')';
            }
            $groups[$key][$container] = $label;
        }

        return $groups;
    }

    /** @param  list<string>  $paths */
    private function mountPoints(ApplicationPage $page, Device $device, array $paths): void
    {
        $storage = $device->storage()->get()->keyBy('storage_descr');
        $rows = [];
        foreach ($paths as $path) {
            $mount = rtrim($path, '/') ?: '/';
            while (! $storage->has($mount) && dirname($mount) !== $mount) {
                $mount = dirname($mount);
            }
            $disk = $storage->get($mount);
            $rows[] = [
                $path,
                view('apps.storage-usage', ['path' => $disk ? $mount : $path, 'storage' => $disk]),
            ];
        }
        $page->addTable('Mount Points', ['Path', 'Mount Point and Usage'], $rows);
    }

    /** @param  list<string|array<string, string>>  $addresses */
    private function network(ApplicationPage $page, Device $device, array $addresses): void
    {
        $rows = [];
        foreach ($addresses as $address) {
            $info = is_array($address) ? $address : ['ip' => $address];
            if (empty($info['ip'])) {
                continue;
            }
            $interface = $info['if'] ?? '';
            $gateway = $info['gw_if'] ?? '';
            $port = $this->findPort($device, $interface);
            $rows[] = [
                $info['ip'],
                view('apps.port-name', ['name' => $interface, 'port' => $port]),
                $port?->ifSpeed,
                $port?->ifInUcastPkts_rate,
                $port?->ifOutUcastPkts_rate,
                $port?->ifInOctets_rate,
                $port?->ifOutOctets_rate,
                $port?->ifInErrors_rate,
                $port?->ifOutErrors_rate,
                $info['gw'] ?? '',
                view('apps.port-name', ['name' => $gateway, 'port' => $this->findPort($device, $gateway)]),
            ];
        }
        $page->addTable('Network Interfaces', [
            'IP', 'Interface', 'Speed', 'Pkts/Sec In', 'Pkts/Sec Out', 'Bytes/Sec In', 'Bytes/Sec Out',
            'Errors/Sec In', 'Errors/Sec Out', 'Gateway', 'GW If',
        ], $rows);
    }

    private function findPort(Device $device, string $interface): ?Port
    {
        if ($interface === '') {
            return null;
        }

        // A FreeBSD guest reports its side of the epair. The host reports the opposite side.
        if ($device->os === 'freebsd' && preg_match('/^epair.*([ab])$/', $interface, $matches)) {
            $interface = substr($interface, 0, -1) . ($matches[1] === 'a' ? 'b' : 'a');
        }

        return PortCache::getByIfName($interface, $device);
    }
}
