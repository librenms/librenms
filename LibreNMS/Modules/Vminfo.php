<?php

/*
 * Vminfo.php
 *
 * -Description-
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2023 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Modules;

use App\Models\Device;
use App\Observers\ModuleModelObserver;
use LibreNMS\Data\Metrics\MetricCollector;
use LibreNMS\Data\Metrics\MetricWriter;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Data\WriteInterface;
use LibreNMS\Interfaces\Discovery\VminfoDiscovery;
use LibreNMS\Interfaces\Polling\VminfoPolling;
use LibreNMS\OS;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Traits\RequiresPollingMethod;

class Vminfo implements \LibreNMS\Interfaces\Module
{
    use SyncsModels;
    use RequiresPollingMethod;

    /**
     * @inheritDoc
     */
    public function dependencies(): array
    {
        return [];
    }

    public function pollingMethods(Device $device): array
    {
        return [PollingMethodType::Proxmox, PollingMethodType::Snmp];
    }

    public function shouldDiscover(OS $os): bool
    {
        return $os->dataSources(VminfoDiscovery::class) !== [];
    }

    /**
     * Each source syncs only the VMs of its types, so VMs from several sources are merged.
     */
    public function discover(OS $os): void
    {
        $device = $os->getDevice();
        ModuleModelObserver::observe(\App\Models\Vminfo::class);

        foreach ($os->dataSources(VminfoDiscovery::class) as $source) {
            $types = $source->vmTypes();
            $vms = $source->discoverVminfo()->whereIn('vm_type', $types);

            $this->syncModels($device, 'vminfo', $vms, $device->vminfo->whereIn('vm_type', $types));
        }
    }

    public function shouldPoll(OS $os): bool
    {
        // without discovered VMs there is nothing to poll, don't check the sources
        return $this->dataExists($os->getDevice())
            && $os->dataSources(VminfoPolling::class) !== [];
    }

    /**
     * @inheritDoc
     */
    public function poll(OS $os, DataStorageInterface $datastore): void
    {
        $device = $os->getDevice();
        $metrics = new MetricCollector([
            'vm.cpu',
            'vm.memory',
            'vm.disk',
            'vm.diskio',
            'vm.net',
        ]);
        ModuleModelObserver::observe(\App\Models\Vminfo::class);

        foreach ($os->dataSources(VminfoPolling::class) as $source) {
            $existing = $device->vminfo->whereIn('vm_type', $source->vmTypes());
            if ($existing->isEmpty()) {
                continue;
            }

            $vms = $source->pollVminfo($existing->values(), $metrics);
            $this->syncModels($device, 'vminfo', $vms, $existing);
        }

        if ($datastore instanceof WriteInterface) {
            $writer = new MetricWriter($metrics, $datastore);
            $writer->writeMetric('vm.cpu', [
                'rrd_def' => RrdDefinition::make()
                    ->addDataset('usage', 'GAUGE', 0),
            ]);
            $writer->writeMetric('vm.memory', [
                'rrd_def' => RrdDefinition::make()
                    ->addDataset('used', 'GAUGE', 0)
                    ->addDataset('total', 'GAUGE', 0),
            ]);
            $writer->writeMetric('vm.disk', [
                'rrd_def' => RrdDefinition::make()
                    ->addDataset('used', 'GAUGE', 0)
                    ->addDataset('total', 'GAUGE', 0),
            ]);
            $writer->writeMetric('vm.diskio', [
                'rrd_def' => RrdDefinition::make()
                    ->addDataset('read_bytes', 'COUNTER', 0)
                    ->addDataset('write_bytes', 'COUNTER', 0)
                    ->addDataset('read_ops', 'COUNTER', 0)
                    ->addDataset('write_ops', 'COUNTER', 0)
                    ->addDataset('read_time_ns', 'COUNTER', 0)
                    ->addDataset('write_time_ns', 'COUNTER', 0)
                    ->addDataset('failed_reads', 'COUNTER', 0)
                    ->addDataset('failed_writes', 'COUNTER', 0),
            ]);
            $writer->writeMetric('vm.net', [
                'rrd_def' => RrdDefinition::make()
                    ->addDataset('in', 'COUNTER', 0)
                    ->addDataset('out', 'COUNTER', 0),
            ]);
        }
    }

    public function dataExists(Device $device): bool
    {
        return $device->vminfo()->exists();
    }

    /**
     * @inheritDoc
     */
    public function cleanup(Device $device): int
    {
        return $device->vminfo()->delete();
    }

    /**
     * @inheritDoc
     */
    public function dump(Device $device, string $type): ?array
    {
        return [
            'vminfo' => $device->vminfo()->orderBy('vmwVmVMID')
                ->get()->map->makeHidden(['id', 'device_id']),
        ];
    }
}
