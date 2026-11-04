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
use App\Models\Eventlog;
use App\Observers\ModuleModelObserver;
use Illuminate\Support\Collection;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\PowerState;
use LibreNMS\Enum\ProcessType;
use LibreNMS\Enum\Severity;
use LibreNMS\Interfaces\Data\WriteInterface;
use LibreNMS\Interfaces\Discovery\VminfoDiscovery;
use LibreNMS\Interfaces\Polling\VminfoPolling;
use LibreNMS\Interfaces\UsesDataSources;
use LibreNMS\Interfaces\VminfoClusterSource;
use LibreNMS\Interfaces\VminfoSource;
use LibreNMS\OS;
use LibreNMS\Polling\Method\PollingMethodRegistry;
use LibreNMS\RRD\RrdDefinition;
use Throwable;

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

    public function removeMethodData(Device $device, PollingMethodType $type): int
    {
        $source = app(PollingMethodRegistry::class)->get($type)->dataSourceClass();

        if ($source === null || ! is_a($source, VminfoSource::class, true)) {
            return 0;
        }

        return $device->vminfo()->whereIn('vm_type', $source::vmTypes())->delete();
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
            $this->isolated($device, $source, function () use ($source, $device): void {
                $types = $source::vmTypes();
                $vms = $source->discoverVminfo()->whereIn('vm_type', $types);

                if ($source instanceof VminfoClusterSource) {
                    $this->syncClusterVms($device, $source, $vms);

                    return;
                }

                $this->syncModels($device, 'vminfo', $vms, $device->vminfo->whereIn('vm_type', $types));
            });
        }
    }

    public function shouldPoll(OS $os): bool
    {
        // without discovered VMs there is nothing to poll, don't check the sources
        // but a VM may migrate to a node of a cluster that has no VMs yet
        if (! $this->dataExists($os->getDevice()) && $os->dataSources(VminfoClusterSource::class) === []) {
            return false;
        }

        return $os->dataSources(VminfoPolling::class) !== [];
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
            $this->isolated($device, $source, function () use ($source, $device, $metrics): void {
                if ($source instanceof VminfoClusterSource) {
                    $this->claimMigratedVms($device, $source->clusterId(), $source->localVmKeys());
                }

                $existing = $device->vminfo->whereIn('vm_type', $source::vmTypes());
                if ($existing->isEmpty()) {
                    return;
                }

                $vms = $source->pollVminfo($existing->values(), $metrics);
                $this->syncModels($device, 'vminfo', $vms, $existing);
            });
        }

        foreach ($metrics->entries() as $entry) {
            $metric = $entry->identity;
            $datastore->write($metric->measurement, $entry->fields, $metric->tags, [
                'rrd_dir' => $metric->rrdDir,
                'rrd_name' => $metric->rrdName,
                'rrd_def' => $definitions[$metric->measurement],
            ]);
        }
    }

    /**
     * Sync the VMs of one node of a cluster. A VM on another node is not removed: that node claims it, also if it is offline now.
     *
     * @param  Collection<int, \App\Models\Vminfo>  $vms  the VMs of this node
     */
    private function syncClusterVms(Device $device, VminfoClusterSource $source, Collection $vms): void
    {
        $local = $vms->map->getCompositeKey()->all();
        $this->claimMigratedVms($device, $source->clusterId(), $local);

        $inCluster = [...$source->clusterVmKeys(), ...$local];
        \App\Models\Vminfo::where('cluster_id', $source->clusterId())->get()
            ->reject(fn (\App\Models\Vminfo $vm): bool => in_array($vm->getCompositeKey(), $inCluster, true))
            ->each->delete();
        $device->unsetRelation('vminfo');

        [$here, $elsewhere] = $device->vminfo->whereIn('vm_type', $source::vmTypes())
            ->partition(fn (\App\Models\Vminfo $vm): bool => in_array($vm->getCompositeKey(), $local, true));

        // no node runs it right now, or the node that does is not polled
        $elsewhere->each(fn (\App\Models\Vminfo $vm) => $vm->fill(['vmwVmState' => PowerState::UNKNOWN])->save());

        $this->syncModels($device, 'vminfo', $vms, $here);
    }

    /**
     * Move the VMs that migrated to this node here, they keep their row and metrics.
     *
     * @param  list<string>  $local  the composite keys of the VMs on this node
     */
    private function claimMigratedVms(Device $device, string $clusterId, array $local): void
    {
        \App\Models\Vminfo::where('cluster_id', $clusterId)->where('device_id', '!=', $device->device_id)->get()
            ->filter(fn (\App\Models\Vminfo $vm): bool => in_array($vm->getCompositeKey(), $local, true))
            ->each(function (\App\Models\Vminfo $vm) use ($device): void {
                $vm->device_id = $device->device_id;
                $vm->save();
            });

        $device->unsetRelation('vminfo');
    }

    /**
     * A source that fails must not stop the other sources or lose their metrics.
     *
     * @param  callable(): void  $work
     */
    private function isolated(Device $device, object $source, callable $work): void
    {
        try {
            $work();
        } catch (Throwable $e) {
            Eventlog::log('Error reading VMs from ' . class_basename($source) . ': ' . class_basename($e) . '. Check log file for more details.', $device, 'vminfo', Severity::Error);
            report($e);
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
                ->addDataset('write_bytes', 'DERIVE', 0),
            'vm.diskops' => RrdDefinition::make()
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
                ->get()->map->makeHidden(['id', 'device_id', 'cluster_id']), // only sources without test data set the cluster
        ];
    }
}
