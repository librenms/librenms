<?php

namespace LibreNMS\Polling\Method\DataSources;

use App\ApiClients\Data\ProxmoxFilesystem;
use App\ApiClients\Data\ProxmoxGuest;
use App\ApiClients\ProxmoxApi;
use App\Data\TimeSeries\MetricCollector;
use App\Models\Vminfo;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use LibreNMS\Interfaces\Discovery\VminfoDiscovery;
use LibreNMS\Interfaces\Polling\VminfoPolling;
use LibreNMS\Interfaces\VminfoClusterSource;
use LibreNMS\Polling\Method\Config\ProxmoxApiConfig;

/**
 * Module data of one Proxmox VE node, read with the Proxmox API. Each node reports the guests it runs.
 * A standalone host is a cluster of one.
 */
class ProxmoxApiDataSource implements VminfoClusterSource, VminfoDiscovery, VminfoPolling
{
    private const TYPE_VM = 'proxmox';
    private const TYPE_CONTAINER = 'proxmox-lxc';
    private const SKIPPED_FILESYSTEMS = ['tmpfs', 'devtmpfs', 'squashfs', 'overlay', 'proc', 'sysfs', 'cgroup', 'cgroup2'];

    private readonly ProxmoxApi $api;

    /** @var array{proxmox: array<int, ProxmoxGuest>, proxmox-lxc: array<int, ProxmoxGuest>}|null */
    private ?array $localGuests = null;

    public function __construct(
        ProxmoxApiConfig $config,
        private readonly string $clusterId,
    ) {
        $this->api = new ProxmoxApi($config);
    }

    public function clusterId(): string
    {
        return $this->clusterId;
    }

    /**
     * @return list<string>
     */
    public function clusterVmKeys(): array
    {
        $guests = $this->api->listClusterGuests();

        return [...$this->vmKeys(self::TYPE_VM, $guests['qemu']), ...$this->vmKeys(self::TYPE_CONTAINER, $guests['lxc'])];
    }

    /**
     * @return list<string>
     */
    public function localVmKeys(): array
    {
        $guests = $this->localGuests();

        return [...$this->vmKeys(self::TYPE_VM, $guests[self::TYPE_VM]), ...$this->vmKeys(self::TYPE_CONTAINER, $guests[self::TYPE_CONTAINER])];
    }

    /**
     * @return string[]
     */
    public static function vmTypes(): array
    {
        return [self::TYPE_VM, self::TYPE_CONTAINER];
    }

    /**
     * The guests of this node.
     *
     * @return Collection<int, Vminfo>
     */
    public function discoverVminfo(): Collection
    {
        $guests = $this->localGuests();
        $vms = array_filter($guests[self::TYPE_VM], fn (ProxmoxGuest $vm): bool => ! $vm->template);
        $running = array_filter($vms, fn (ProxmoxGuest $vm): bool => $vm->isRunning());
        $guestOs = $this->api->getVmsGuestOs(array_column($running, 'id'));

        $containers = array_filter($guests[self::TYPE_CONTAINER], fn (ProxmoxGuest $container): bool => ! $container->template);
        $containerOs = $this->api->getContainersOsType(array_column($containers, 'id'));

        return (new Collection($vms))
            ->map(fn (ProxmoxGuest $vm): Vminfo => $this->makeVminfo(self::TYPE_VM, $vm, $guestOs[$vm->id] ?? ''))
            ->concat(array_map(fn (ProxmoxGuest $container): Vminfo => $this->makeVminfo(self::TYPE_CONTAINER, $container, $containerOs[$container->id] ?? ''), $containers))
            ->values();
    }

    /**
     * @param  Collection<int, Vminfo>  $vms
     * @param  MetricCollector<Vminfo>  $metrics
     * @return Collection<int, Vminfo>
     */
    public function pollVminfo(Collection $vms, MetricCollector $metrics): Collection
    {
        $guests = $this->localGuests();

        $polledVms = $vms->where('vm_type', self::TYPE_VM);
        $polledGuests = array_intersect_key($guests[self::TYPE_VM], $polledVms->keyBy('vmwVmVMID')->all());
        $running = array_filter($polledGuests, fn (ProxmoxGuest $guest): bool => $guest->isRunning());
        $filesystems = $this->api->getVmsFsInfo(array_keys($running));

        foreach ($vms as $vm) {
            $guest = $guests[$vm->vm_type][$vm->vmwVmVMID] ?? null;

            // no longer on this node, the next discovery knows if it was removed
            if ($guest === null) {
                continue;
            }

            $vm->vmwVmState = $guest->powerState();

            if (! $guest->isRunning()) {
                continue;
            }

            // CPU
            $metrics->record($vm, 'vm.cpu', [
                'usage' => $guest->cpu * 100,
            ]);

            // Memory
            $metrics->record($vm, 'vm.memory', [
                'used' => $guest->memory,
                'total' => $guest->maxMemory,
            ]);

            if ($vm->vm_type === self::TYPE_CONTAINER) {
                $metrics->record($vm, 'vm.diskio', [
                    'read_bytes' => $guest->diskRead,
                    'write_bytes' => $guest->diskWrite,
                ]);
                // the host sees the filesystems of a container
                $this->recordHostDiskUsage($vm, $guest, $metrics);
            } else {
                $this->recordVmDisks($vm, $guest, $filesystems[$guest->id] ?? [], $metrics);
            }

            // Network counters (cumulative since boot)
            $metrics->record($vm, 'vm.net', [
                'in' => $guest->netIn,
                'out' => $guest->netOut,
            ]);
        }

        return $vms;
    }

    /**
     * The lists have all data of the guests, this node is asked once per poll or discovery.
     *
     * @return array{proxmox: array<int, ProxmoxGuest>, proxmox-lxc: array<int, ProxmoxGuest>}
     */
    private function localGuests(): array
    {
        return $this->localGuests ??= [
            self::TYPE_VM => array_column($this->api->listVms(), null, 'id'),
            self::TYPE_CONTAINER => array_column($this->api->listContainers(), null, 'id'),
        ];
    }

    /**
     * Templates are not VMs. The guests on an offline node are not known to be templates, so they stay.
     *
     * @param  array<array-key, ProxmoxGuest>  $guests
     * @return list<string> composite keys of the models
     */
    private function vmKeys(string $type, array $guests): array
    {
        $vms = array_filter($guests, fn (ProxmoxGuest $guest): bool => ! $guest->template);

        return array_values(array_map(fn (ProxmoxGuest $guest): string => "$type-$guest->id", $vms));
    }

    /**
     * An unknown OS is left out, so it does not replace the OS found while the guest was running.
     */
    private function makeVminfo(string $type, ProxmoxGuest $guest, string $os): Vminfo
    {
        $vminfo = new Vminfo([
            'cluster_id' => $this->clusterId,
            'vm_type' => $type,
            'vmwVmVMID' => $guest->id,
            'vmwVmDisplayName' => $guest->name,
            'vmwVmMemSize' => $guest->maxMemory / 1048576,
            'vmwVmCpus' => $guest->cpus,
            'vmwVmState' => $guest->powerState(),
        ]);

        if ($os !== '') {
            $vminfo->vmwVmGuestOS = $os;
        }

        return $vminfo;
    }

    /**
     * @param  list<ProxmoxFilesystem>  $filesystems
     * @param  MetricCollector<Vminfo>  $metrics
     */
    private function recordVmDisks(Vminfo $vm, ProxmoxGuest $guest, array $filesystems, MetricCollector $metrics): void
    {
        // Disk Activity
        $stats = $this->aggregateDiskStats($guest);
        $metrics->record($vm, 'vm.diskio', Arr::only($stats, ['read_bytes', 'write_bytes']));
        $metrics->record($vm, 'vm.diskops', Arr::except($stats, ['read_bytes', 'write_bytes']));

        // Disk usage
        // prefer guest agent (accurate per-filesystem data), skipping pseudo-mounts and repeated mounts of a device
        $real = (new Collection($filesystems))
            ->reject(fn (ProxmoxFilesystem $filesystem): bool => in_array($filesystem->type, self::SKIPPED_FILESYSTEMS, true))
            ->unique('device');
        $diskTotal = $real->sum('totalBytes');

        if ($diskTotal > 0) {
            $metrics->record($vm, 'vm.disk', [
                'used' => $real->sum('usedBytes'),
                'total' => $diskTotal,
            ]);

            return;
        }

        // fall back to hypervisor-level values if agent is absent/unavailable or reported no sizes.
        $this->recordHostDiskUsage($vm, $guest, $metrics);
    }

    /**
     * @param  MetricCollector<Vminfo>  $metrics
     */
    private function recordHostDiskUsage(Vminfo $vm, ProxmoxGuest $guest, MetricCollector $metrics): void
    {
        $metrics->record($vm, 'vm.disk', [
            'used' => $guest->disk,
            'total' => $guest->maxDisk,
        ]);
    }

    /**
     * @return array<string, int>
     */
    private function aggregateDiskStats(ProxmoxGuest $guest): array
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

        foreach ($guest->blockStats as $stat) {
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
