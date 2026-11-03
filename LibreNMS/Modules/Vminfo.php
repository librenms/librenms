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

use App\Data\TimeSeries\MetricCollector;
use App\Models\Device;
use App\Observers\ModuleModelObserver;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\ProcessType;
use LibreNMS\Interfaces\Data\WriteInterface;
use LibreNMS\Interfaces\Discovery\VminfoDiscovery;
use LibreNMS\Interfaces\Polling\VminfoPolling;
use LibreNMS\Interfaces\UsesDataSources;
use LibreNMS\OS;
use LibreNMS\Polling\Method\PollingMethodRegistry;
use LibreNMS\RRD\RrdDefinition;

class Vminfo implements \LibreNMS\Interfaces\Module, UsesDataSources
{
    use SyncsModels;

    /**
     * @inheritDoc
     */
    public function dependencies(): array
    {
        return [];
    }

    public function pollingMethods(Device $device): array
    {
        // the OS is mostly a data source with snmp
        return [...app(PollingMethodRegistry::class)->providing(VminfoDiscovery::class), PollingMethodType::Snmp];
    }

    public function dataSourceInterface(ProcessType $type): string
    {
        return match ($type) {
            ProcessType::Discovery => VminfoDiscovery::class,
            ProcessType::Poller => VminfoPolling::class,
        };
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
    public function poll(OS $os, WriteInterface $datastore): void
    {
        $device = $os->getDevice();
        $definitions = $this->metricDefinitions();
        $metrics = new MetricCollector(array_keys($definitions));
        ModuleModelObserver::observe(\App\Models\Vminfo::class);

        foreach ($os->dataSources(VminfoPolling::class) as $source) {
            $existing = $device->vminfo->whereIn('vm_type', $source->vmTypes());
            if ($existing->isEmpty()) {
                continue;
            }

            $vms = $source->pollVminfo($existing->values(), $metrics);
            $this->syncModels($device, 'vminfo', $vms, $existing);
        }

        foreach ($metrics->entries() as $entry) {
            $metric = $entry->identity;
            $datastore->write($metric->measurement, $entry->fields, $metric->tags, [
                'rrd_name' => $metric->rrdName,
                'rrd_def' => $definitions[$metric->measurement],
            ]);
        }
    }

    /**
     * The measurements a data source may record.
     *
     * @return array<string, RrdDefinition>
     */
    private function metricDefinitions(): array
    {
        return [
            'vm.cpu' => RrdDefinition::make()
                ->addDataset('usage', 'GAUGE', 0),
            'vm.memory' => RrdDefinition::make()
                ->addDataset('used', 'GAUGE', 0)
                ->addDataset('total', 'GAUGE', 0),
            'vm.disk' => RrdDefinition::make()
                ->addDataset('used', 'GAUGE', 0)
                ->addDataset('total', 'GAUGE', 0),
            'vm.diskio' => RrdDefinition::make()
                ->addDataset('read_bytes', 'DERIVE', 0)
                ->addDataset('write_bytes', 'DERIVE', 0)
                ->addDataset('read_ops', 'DERIVE', 0)
                ->addDataset('write_ops', 'DERIVE', 0)
                ->addDataset('read_time_ns', 'DERIVE', 0)
                ->addDataset('write_time_ns', 'DERIVE', 0)
                ->addDataset('failed_reads', 'DERIVE', 0)
                ->addDataset('failed_writes', 'DERIVE', 0),
            'vm.net' => RrdDefinition::make()
                ->addDataset('in', 'DERIVE', 0)
                ->addDataset('out', 'DERIVE', 0),
        ];
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
