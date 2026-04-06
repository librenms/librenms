<?php

namespace LibreNMS\OS;

use App\ApiClients\ProxmoxApi;
use App\Models\Vminfo;
use Illuminate\Support\Collection;
use LibreNMS\Data\Metrics\MetricCollector;
use LibreNMS\Enum\PowerState;
use LibreNMS\Interfaces\Discovery\VminfoDiscovery;
use LibreNMS\Interfaces\Polling\VminfoPolling;
use LibreNMS\OS;

class Proxmox extends OS implements VminfoDiscovery, VminfoPolling
{
    public function discoverVminfo(): Collection
    {
        $vms = new Collection;
        $api = new ProxmoxApi($this->getDevice()->hostname, 30);

        foreach ($api->listVms() as $vm) {
            $vms->push(new Vminfo([
                'vm_type' => 'proxmox',
                'vmwVmVMID' => $vm['vmid'],
                'vmwVmDisplayName' => $vm['name'],
                'vmwVmMemSize' => $vm['maxmem'] / 1048576,
                'vmwVmCpus' => $vm['cpus'],
                'vmwVmGuestOS' => $api->getVmGuestOs($vm['vmid']),
                'vmwVmState' => PowerState::parse($vm['status']),
            ]));
        }

        return $vms;
    }

    /**
     * @param  Collection<Vminfo>  $vms
     * @param  MetricCollector<Vminfo>  $metrics
     * @return Collection<Vminfo>
     */
    public function pollVminfo(Collection $vms, MetricCollector $metrics): Collection
    {
        $api = new ProxmoxApi($this->getDevice()->hostname, 5);

        foreach ($vms as $vm) {
            $status = $api->getVmStatus($vm->vmwVmVMID);

            if (($status['status'] ?? 'stopped') === 'stopped') {
                continue;
            }
            dump("Geting vm: $vm->vmwVmVMID ($vm->vmwVmDisplayName)");

            // CPU
            $metrics->record($vm, 'vm.cpu', [
                'usage' => ($status['cpu'] ?? 0) * 100,
            ]);

            // Memory
            $metrics->record($vm, 'vm.memory', [
                'used' => $status['mem'] ?? 0,
                'total' => $status['maxmem'] ?? 0,
            ]);

            // Disk Activity
//            $metrics->record($vm, 'vm.diskio', [
//                'read' => $status['diskread'] ?? 0,
//                'write' => $status['diskwrite'] ?? 0,
//            ]);
            $metrics->record($vm, 'vm.diskio', $this->aggregateDiskStats($status));


            // Disk usage
            // prefer guest agent (accurate per-filesystem data),
            // fall back to hypervisor-level values if agent is absent/unavailable.
            $fsInfo = $api->getVmFsInfo($vm->vmwVmVMID);

            if (!empty($fsInfo)) {
                // Aggregate across all real filesystems, skipping pseudo-mounts
                $skipTypes = ['tmpfs', 'devtmpfs', 'squashfs', 'overlay', 'proc', 'sysfs', 'cgroup', 'cgroup2'];
                $diskUsed  = 0;
                $diskTotal = 0;

                foreach ($fsInfo as $fs) {
                    if (in_array($fs['type'] ?? '', $skipTypes, true)) {
                        continue;
                    }
                    $diskUsed  += $fs['used-bytes']  ?? 0;
                    $diskTotal += $fs['total-bytes'] ?? 0;
                }

                $metrics->record($vm, 'vm.disk', [
                    'used' =>  $diskUsed,
                    'total' => $diskTotal,
                ]);
            } else {
                // Hypervisor fallback
                $metrics->record($vm, 'vm.disk', [
                    'used' => $status['disk'] ?? 0,
                    'total' => $status['maxdisk'] ?? 0,
                ]);
            }

            // Network counters (cumulative since boot)
            $metrics->record($vm, 'vm.net', [
                'in' => $status['netin'] ?? 0,
                'out' => $status['netout'] ?? 0,
            ]);
        }

        return $vms;
    }

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
