<?php

namespace LibreNMS\Applications;

use App\Facades\PortCache;
use App\Models\Application;
use App\Models\Port;

class HvMonitor extends ApplicationDefinition
{
    public const string TYPE = 'hv-monitor';
    protected const ?string DOCS = 'HV Monitor';
    protected const ?string NAME = 'HV Monitor';

    protected const ?array OVERVIEW = ['status', 'memory', 'pmem', 'time', 'pcpu', 'flt', 'csw', 'cow', 'etimes', 'snaps', 'snaps_size'];

    protected const array GRAPHS = [
        'memory' => 'Memory Usage',
        'pmem' => 'Memory Percent',
        'time' => 'CPU Time',
        'pcpu' => 'CPU Percent',
        'flt' => 'Faults',
        'csw' => 'Context Switches',
        'etimes' => 'Elapsed Time',
    ];

    private const array SECTIONS = [
        'general' => 'General',
        'disk' => 'Disk',
        'network' => 'Network',
        'snapshots' => 'Snapshots',
    ];

    private const array NETWORK_GRAPHS = [
        'net-pkts' => 'Packets',
        'net-bytes' => 'Bytes',
        'net-errs' => 'Errors',
        'net-drops' => 'Drops',
        'net-coll' => 'Collisions',
    ];

    protected function buildDevicePage(ApplicationPage $page, Application $application): void
    {
        $data = $application->data ?? [];
        $hv = $data['hv'] ?? '';
        $vms = $data['VMs'] ?? [];
        $vm = $page->option('vm');
        $disk = $page->option('vmdisk');
        $interface = $page->option('vmif');
        $section = $page->option('vmpage') ?? 'general';
        $page->addFilter('VMs', 'vm', ['' => 'Totals'] + array_combine($vms, $vms), $vm);
        $page->addFilter('Pages', 'vmpage', self::SECTIONS, $disk === null && $interface === null ? $section : null, ['vm' => $vm]);

        if ($vm !== null) {
            $disks = $data['VMdisks'][$vm] ?? [];
            $interfaces = $data['VMifs'][$vm] ?? [];
            $page->addFilter('Disks', 'vmdisk', array_combine($disks, $disks), $disk, ['vm' => $vm]);
            $names = array_keys($interfaces);
            $page->addFilter('Interfaces', 'vmif', array_combine($names, $names), $interface, ['vm' => $vm]);

            if ($interface !== null && isset($interfaces[$interface])) {
                $this->interfaceDetails($page, $application, $interfaces[$interface]);
            }
        }

        $graphs = match (true) {
            $disk !== null => $this->diskGraphs($hv, true),
            $interface !== null => self::NETWORK_GRAPHS,
            $section === 'disk' => $this->diskGraphs($hv, false),
            $section === 'snapshots' => $hv === 'CBSD' ? ['snaps' => 'Snapshots', 'snaps_size' => 'Snapshots Size'] : ['snaps' => 'Snapshots'],
            $section === 'network' => array_map(fn (string $title) => 'Network ' . $title, self::NETWORK_GRAPHS),
            default => $this->generalGraphs($vm, $hv),
        };
        $page->addGraphs($graphs, ['hv' => $hv, 'vm' => $vm, 'vmdisk' => $disk, 'vmif' => $interface]);
    }

    /** @return array<string, string> */
    private function generalGraphs(?string $vm, string $hv): array
    {
        $status = match (true) {
            $vm === null => ['status' => 'VM Statuses Count'],
            $hv === 'libvirt' => ['status-int' => 'VM Status: 0=no state, 1=running, 2=blocked, 3=paused, 4=being shut down, 5=shut off, 6=crashed, 7=PM suspended'],
            $hv === 'CBSD' => ['status-int' => 'VM Status: 1=Running, 8=Shut Off, 9=Maintenance'],
            default => [],
        };

        return $status + self::GRAPHS;
    }

    /** @return array<string, string> */
    private function diskGraphs(string $hv, bool $selected): array
    {
        $graphs = $selected ? ['disk-size' => 'Size'] : ['disk-size2' => 'Disk Size'];
        if (! $selected && $hv !== 'libvirt') {
            $graphs['disk-rw-blocks'] = 'Disk RW, Blocks';
        }
        $graphs += ['disk-rw-bytes' => 'Disk RW, Bytes', 'disk-rw-reqs' => 'Disk RW, Requests'];
        if (! $selected) {
            $graphs['cow'] = 'COWs';
        }
        if (($hv === 'CBSD') !== $selected) {
            $graphs['disk-rw-time'] = 'Disk RW, Time';
        }
        if ($hv === 'libvirt') {
            $graphs += ['disk-ftime' => 'Disk Flush, Time', 'disk-freqs' => 'Disk Flush, Requests'];
        }

        return $graphs;
    }

    /** @param  array<string, string>  $info */
    private function interfaceDetails(ApplicationPage $page, Application $application, array $info): void
    {
        $mac = $info['mac'] ?? '';
        $port = $mac === '' ? null : Port::with('device')->firstWhere('ifPhysAddress', str_replace(':', '', $mac));
        $page->details['MAC'] = view('apps.port-reference', ['label' => $mac, 'port' => $port]);
        foreach (['if' => 'HV interface', 'parent' => 'HV parent interface'] as $key => $label) {
            if (! empty($info[$key])) {
                $port = PortCache::getByIfName($info[$key], $application->device_id);
                $page->details[$label] = view('apps.port-name', ['name' => $info[$key], 'port' => $port]);
            }
        }
    }
}
