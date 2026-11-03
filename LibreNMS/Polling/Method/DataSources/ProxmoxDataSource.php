<?php

namespace LibreNMS\Polling\Method\DataSources;

use App\ApiClients\ProxmoxApi;
use App\Data\TimeSeries\MetricCollector;
use App\Models\Vminfo;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use LibreNMS\Enum\PowerState;
use LibreNMS\Interfaces\Discovery\VminfoDiscovery;
use LibreNMS\Interfaces\Polling\VminfoPolling;
use LibreNMS\Polling\Method\Config\ProxmoxConfig;

/**
 * Module data of a Proxmox VE host, read with the Proxmox API.
 */
class ProxmoxDataSource implements VminfoDiscovery, VminfoPolling
{
    private const TYPE_VM = 'proxmox';
    private const TYPE_CONTAINER = 'proxmox-lxc';

    private readonly ProxmoxApi $api;

    public function __construct(ProxmoxConfig $config)
    {
        $this->api = new ProxmoxApi($config);
    }

    /**
     * @return string[]
     */
    public function vmTypes(): array
    {
        return [self::TYPE_VM, self::TYPE_CONTAINER];
    }

    /**
     * @return Collection<int, Vminfo>
     */
    public function discoverVminfo(): Collection
    {
        $vms = $this->api->listVms();
        $running = array_filter($vms, fn (array $vm): bool => $vm['status'] === 'running');
        $guestOs = $this->api->getVmsGuestOs(array_column($running, 'vmid'));

        $containers = $this->api->listContainers();
        $containerOs = $this->api->getContainersOsType(array_column($containers, 'vmid'));

        return (new Collection($vms))
            ->map(fn (array $vm): Vminfo => $this->makeVminfo(self::TYPE_VM, $vm, $guestOs[$vm['vmid']] ?? ''))
            ->concat(array_map(fn (array $container): Vminfo => $this->makeVminfo(self::TYPE_CONTAINER, $container, $containerOs[$container['vmid']] ?? ''), $containers));
    }

    /**
     * @param  Collection<int, Vminfo>  $vms
     * @param  MetricCollector<Vminfo>  $metrics
     * @return Collection<int, Vminfo>
     */
    public function pollVminfo(Collection $vms, MetricCollector $metrics): Collection
    {
        $polledVms = $vms->where('vm_type', self::TYPE_VM);
        $statuses = [
            self::TYPE_VM => $polledVms->isEmpty() ? [] : array_column($this->api->listVms(full: true), null, 'vmid'),
            self::TYPE_CONTAINER => $vms->contains('vm_type', self::TYPE_CONTAINER) ? array_column($this->api->listContainers(), null, 'vmid') : [],
        ];

        $vmStatuses = array_intersect_key($statuses[self::TYPE_VM], $polledVms->keyBy('vmwVmVMID')->all());
        $running = array_filter($vmStatuses, fn (array $status): bool => ($status['status'] ?? 'stopped') !== 'stopped');
        $fsInfos = $this->api->getVmsFsInfo(array_keys($running));

        foreach ($vms as $vm) {
            $status = $statuses[$vm->vm_type][$vm->vmwVmVMID] ?? null;

            // removed since discovery, the next discovery deletes it
            if ($status === null) {
                continue;
            }

            $state = $status['status'] ?? 'stopped';
            $vm->vmwVmState = PowerState::parse($state);

            if ($state === 'stopped') {
                continue;
            }

            // CPU
            $metrics->record($vm, 'vm.cpu', [
                'usage' => ($status['cpu'] ?? 0) * 100,
            ]);

            // Memory
            $metrics->record($vm, 'vm.memory', [
                'used' => $status['mem'] ?? 0,
                'total' => $status['maxmem'] ?? 0,
            ]);

            if ($vm->vm_type === self::TYPE_CONTAINER) {
                $this->recordContainerDisks($vm, $status, $metrics);
            } else {
                $this->recordVmDisks($vm, $status, $fsInfos[$vm->vmwVmVMID] ?? [], $metrics);
            }

            // Network counters (cumulative since boot)
            $metrics->record($vm, 'vm.net', [
                'in' => $status['netin'] ?? 0,
                'out' => $status['netout'] ?? 0,
            ]);
        }

        return $vms;
    }

    /**
     * @param  array<string, mixed>  $guest
     */
    private function makeVminfo(string $type, array $guest, string $os): Vminfo
    {
        return new Vminfo([
            'vm_type' => $type,
            'vmwVmVMID' => $guest['vmid'],
            'vmwVmDisplayName' => $guest['name'],
            'vmwVmMemSize' => $guest['maxmem'] / 1048576,
            'vmwVmCpus' => $guest['cpus'],
            'vmwVmGuestOS' => $os,
            'vmwVmState' => PowerState::parse($guest['status']),
        ]);
    }

    /**
     * The host sees the filesystems of a container, it does not report its disk operations.
     *
     * @param  array<string, mixed>  $status
     * @param  MetricCollector<Vminfo>  $metrics
     */
    private function recordContainerDisks(Vminfo $vm, array $status, MetricCollector $metrics): void
    {
        $metrics->record($vm, 'vm.diskio', [
            'read_bytes' => $status['diskread'] ?? 0,
            'write_bytes' => $status['diskwrite'] ?? 0,
        ]);

        $metrics->record($vm, 'vm.disk', [
            'used' => $status['disk'] ?? 0,
            'total' => $status['maxdisk'] ?? 0,
        ]);
    }

    /**
     * @param  array<string, mixed>  $status
     * @param  list<array<string, mixed>>  $fsInfo
     * @param  MetricCollector<Vminfo>  $metrics
     */
    private function recordVmDisks(Vminfo $vm, array $status, array $fsInfo, MetricCollector $metrics): void
    {
        // Disk Activity
        $stats = $this->aggregateDiskStats($status);
        $metrics->record($vm, 'vm.diskio', Arr::only($stats, ['read_bytes', 'write_bytes']));
        $metrics->record($vm, 'vm.diskops', Arr::except($stats, ['read_bytes', 'write_bytes']));

        // Disk usage
        // prefer guest agent (accurate per-filesystem data),
        // fall back to hypervisor-level values if agent is absent/unavailable.
        if (! empty($fsInfo)) {
            // Aggregate across all real filesystems, skipping pseudo-mounts
            $skipTypes = ['tmpfs', 'devtmpfs', 'squashfs', 'overlay', 'proc', 'sysfs', 'cgroup', 'cgroup2'];
            $diskUsed = 0;
            $diskTotal = 0;

            foreach ($fsInfo as $fs) {
                if (in_array($fs['type'] ?? '', $skipTypes, true)) {
                    continue;
                }
                $diskUsed += $fs['used-bytes'] ?? 0;
                $diskTotal += $fs['total-bytes'] ?? 0;
            }

            $metrics->record($vm, 'vm.disk', [
                'used' => $diskUsed,
                'total' => $diskTotal,
            ]);
        } else {
            // Hypervisor fallback
            $metrics->record($vm, 'vm.disk', [
                'used' => $status['disk'] ?? 0,
                'total' => $status['maxdisk'] ?? 0,
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $status
     * @return array<string, int>
     */
    private function aggregateDiskStats(array $status): array
    {
        $data = [
            'read_bytes' => 0,
            'write_bytes' => 0,
            'read_ops' => 0,
            'write_ops' => 0,
            'read_time_ns' => 0,
            'write_time_ns' => 0,
            'failed_reads' => 0,
            'failed_writes' => 0,
        ];

        foreach ($status['blockstat'] ?? [] as $stat) {
            $data['read_bytes'] += ($stat['rd_bytes'] ?? 0);
            $data['write_bytes'] += ($stat['wr_bytes'] ?? 0);
            $data['read_ops'] += ($stat['rd_operations'] ?? 0);
            $data['write_ops'] += ($stat['wr_operations'] ?? 0);
            $data['read_time_ns'] += ($stat['rd_total_time_ns'] ?? 0);
            $data['write_time_ns'] += ($stat['wr_total_time_ns'] ?? 0);
            $data['failed_reads'] += ($stat['failed_rd_operations'] ?? 0);
            $data['failed_writes'] += ($stat['failed_wr_operations'] ?? 0);
        }

        return $data;
    }
}
