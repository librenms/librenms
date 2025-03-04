<?php

/**
 * Ospfv3.php
 *
 * Poll OSPFV3-MIB
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2021 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Modules;

use App\Facades\PortCache;
use App\Models\Device;
use App\Models\Ospfv3Area;
use App\Models\Ospfv3Instance;
use App\Models\Ospfv3Nbr;
use App\Models\Ospfv3Port;
use App\Observers\ModuleModelObserver;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Module;
use LibreNMS\OS;
use LibreNMS\Polling\ModuleStatus;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Util\IP;
use SnmpQuery;

class Ospfv3 implements Module
{
    /**
     * @inheritDoc
     */
    public function dependencies(): array
    {
        return ['ports'];
    }

    public function shouldDiscover(OS $os, ModuleStatus $status): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function discover(OS $os): void
    {
        // no discovery
    }

    public function shouldPoll(OS $os, ModuleStatus $status): bool
    {
        return $status->isEnabledAndDeviceUp($os->getDevice());
    }

    /**
     * @inheritDoc
     */
    public function poll(OS $os, DataStorageInterface $datastore): void
    {
        foreach ($os->getDevice()->getVrfContexts() as $context_name) {
            Log::info('Processes: ');
            ModuleModelObserver::observe(Ospfv3Instance::class);

            // Pull data from device
            $ospf_instances_poll = SnmpQuery::context($context_name)
                ->hideMib()->enumStrings()
                ->walk('OSPFV3-MIB::ospfv3GeneralGroup')->valuesByIndex();

            $ospf_instances = new Collection();
            foreach ($ospf_instances_poll as $ospf_instance_id => $ospf_entry) {
                if (empty($ospf_entry['ospfv3RouterId'])) {
                    continue; // skip invalid data
                }
                foreach (['ospfv3RxNewLsas', 'ospfv3OriginateNewLsas', 'ospfv3AreaBdrRtrStatus', 'ospfv3ExtLsaCount', 'ospfv3ASBdrRtrStatus', 'ospfv3VersionNumber', 'ospfv3AdminStatus'] as $column) {
                    if (! array_key_exists($column, $ospf_entry) || is_null($ospf_entry[$column])) {
                        continue 2; // This column must exist and not be null
                    }
                }
                $ospf_entry['ospfv3RouterId'] = long2ip($ospf_entry['ospfv3RouterId']);
                $ospf_entry['ospfv3_instance_id'] = $ospf_instance_id;

                $instance = Ospfv3Instance::updateOrCreate([
                    'device_id' => $os->getDeviceId(),
                    'ospfv3_instance_id' => $ospf_instance_id,
                    'context_name' => $context_name,
                ], $ospf_entry);

                $ospf_instances->push($instance);
            }

            // cleanup
            $os->getDevice()->ospfv3Instances()
                ->where('context_name', $context_name)
                ->whereNotIn('id', $ospf_instances->pluck('id'))->delete();

            $instance_count = $ospf_instances->count();
            Log::info("Total processes: $instance_count");
            if ($instance_count == 0) {
                // if there are no instances, don't check for areas, neighbors, and ports
                return;
            }

            Log::info('Areas: ');
            ModuleModelObserver::observe(Ospfv3Area::class);

            // Pull data from device
            $ospf_areas = SnmpQuery::context($context_name)
                ->hideMib()->enumStrings()
                ->walk('OSPFV3-MIB::ospfv3AreaTable')
                ->mapTable(function ($ospf_area, $ospf_area_id) use ($context_name, $os) {
                    return Ospfv3Area::updateOrCreate([
                        'device_id' => $os->getDeviceId(),
                        'ospfv3AreaId' => $ospf_area_id,
                        'context_name' => $context_name,
                    ], $ospf_area);
                });

            // cleanup
            $os->getDevice()->ospfv3Areas()
                ->where('context_name', $context_name)
                ->whereNotIn('id', $ospf_areas->pluck('id'))->delete();

            Log::info('Total areas: ' . $ospf_areas->count());

            Log::info('Ports: ');
            ModuleModelObserver::observe(Ospfv3Port::class);

            // Pull data from device
            $ospf_ports = SnmpQuery::context($context_name)
                ->hideMib()->enumStrings()
                ->walk('OSPFV3-MIB::ospfv3IfTable')
                ->mapTable(function ($ospf_port, $ifIndex, $ospf_instance_id) use ($context_name, $os) {
                    // find port_id
                    $ospf_port['port_id'] = (int) PortCache::getIdFromIfIndex($ifIndex, $os->getDeviceId());
                    $ospf_port['ospfv3_instance_id'] = $ospf_instance_id;
                    $ospf_port['ospfv3IfDesignatedRouter'] = long2ip($ospf_port['ospfv3IfDesignatedRouter']);
                    $ospf_port['ospfv3IfBackupDesignatedRouter'] = long2ip($ospf_port['ospfv3IfBackupDesignatedRouter']);
                    $ospf_port['ospfv3AreaScopeLsaCksumSum'] ??= 0;
                    $ospf_port['ospfv3IfIndex'] ??= 0;

                    return Ospfv3Port::updateOrCreate([
                        'device_id' => $os->getDeviceId(),
                        'ospfv3_instance_id' => $ospf_instance_id,
                        'ospfv3_port_id' => $ifIndex,
                        'context_name' => $context_name,
                    ], $ospf_port);
                });

            // cleanup
            $os->getDevice()->ospfv3Ports()
                ->where('context_name', $context_name)
                ->whereNotIn('id', $ospf_ports->pluck('id'))->delete();

            Log::info('Total Ports: ' . $ospf_ports->count());

            Log::info('Neighbours: ');
            ModuleModelObserver::observe(Ospfv3Nbr::class);

            // Pull data from device
            $ospf_neighbours = SnmpQuery::context($context_name)
                ->hideMib()->enumStrings()
                ->walk('OSPFV3-MIB::ospfv3NbrTable')
                ->mapTable(function ($ospf_nbr, $ifIndex, $ospf_instance_id, $ospfv3NbrRtrId) use ($context_name, $os) {
                    // get neighbor port_id
                    // Needs searching by Link-Local addressing, but those do not appear to be indexed.
                    $ip_raw = $ospf_nbr['ospfv3NbrAddress'];
                    $ospf_nbr['ospfv3NbrAddress'] = IP::fromHexString($ip_raw, true)->compressed() ?? IP::parse($ip_raw, true)->compressed() ?? $ospf_nbr['ospfv3NbrAddress'];
                    $ospf_nbr['port_id'] = PortCache::getIdFromIp($ospf_nbr['ospfv3NbrAddress'], $context_name); // search all devices
                    $ospf_nbr['ospfv3_instance_id'] = $ospf_instance_id;
                    $ospf_nbr['ospfv3NbrRtrId'] = long2ip($ospfv3NbrRtrId);

                    return Ospfv3Nbr::updateOrCreate([
                        'device_id' => $os->getDeviceId(),
                        'ospfv3_instance_id' => $ospf_instance_id,
                        'ospfv3_nbr_id' => $ifIndex,
                        'context_name' => $context_name,
                    ], $ospf_nbr);
                });

            // cleanup
            $os->getDevice()->ospfv3Nbrs()
                ->where('context_name', $context_name)
                ->whereNotIn('id', $ospf_neighbours->pluck('id'))->delete();

            Log::info('Total neighbors: ' . $ospf_neighbours->count());

            if ($instance_count > 0) {
                // Create device-wide statistics RRD
                $rrd_def = RrdDefinition::make()
                    ->addDataset('instances', 'GAUGE', 0, 1000000)
                    ->addDataset('areas', 'GAUGE', 0, 1000000)
                    ->addDataset('ports', 'GAUGE', 0, 1000000)
                    ->addDataset('neighbours', 'GAUGE', 0, 1000000);

                $fields = [
                    'instances' => $instance_count,
                    'areas' => $ospf_areas->count(),
                    'ports' => $ospf_ports->count(),
                    'neighbours' => $ospf_neighbours->count(),
                ];

                $tags = compact('rrd_def');
                $datastore->put($os->getDeviceArray(), 'ospf-statistics', $tags, $fields);
            }
        }
    }

    public function dataExists(Device $device): bool
    {
        return $device->ospfv3Ports()->exists()
            || $device->ospfv3Nbrs()->exists()
            || $device->ospfv3Areas()->exists()
            || $device->ospfv3Instances()->exists();
    }

    /**
     * @inheritDoc
     */
    public function cleanup(Device $device): int
    {
        $deleted = $device->ospfv3Ports()->delete();
        $deleted += $device->ospfv3Nbrs()->delete();
        $deleted += $device->ospfv3Areas()->delete();
        $deleted += $device->ospfv3Instances()->delete();

        return $deleted;
    }

    /**
     * @inheritDoc
     */
    public function dump(Device $device, string $type): ?array
    {
        if ($type == 'discovery') {
            return null;
        }

        return [
            'ospfv3_ports' => $device->ospfv3Ports()
                ->leftJoin('ports', 'ospfv3_ports.port_id', 'ports.port_id')
                ->select(['ospfv3_ports.*', 'ifIndex'])
                ->orderBy('ospfv3_port_id')->orderBy('context_name')
                ->get()->map->makeHidden(['id', 'device_id', 'port_id']),
            'ospfv3_instances' => $device->ospfv3Instances()
                ->orderBy('ospfv3_instance_id')->orderBy('context_name')
                ->get()->map->makeHidden(['id', 'device_id']),
            'ospfv3_areas' => $device->ospfv3Areas()
                ->orderBy('ospfv3AreaId')->orderBy('context_name')
                ->get()->map->makeHidden(['id', 'device_id']),
            'ospfv3_nbrs' => $device->ospfv3Nbrs()
                ->orderBy('ospfv3_nbr_id')->orderBy('context_name')
                ->get()->map->makeHidden(['id', 'device_id']),
        ];
    }
}
