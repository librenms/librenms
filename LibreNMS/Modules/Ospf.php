<?php
/**
 * Ospf.php
 *
 * Poll OSPF-MIB
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

use App\Models\Device;
use App\Models\Ipv4Address;
use App\Models\OspfArea;
use App\Models\OspfInstance;
use App\Models\OspfNbr;
use App\Models\OspfPort;
use App\Observers\ModuleModelObserver;
use Illuminate\Support\Collection;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Module;
use LibreNMS\OS;
use LibreNMS\Polling\ModuleStatus;
use LibreNMS\RRD\RrdDefinition;
use SnmpQuery;

class Ospf implements Module
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
            echo ' Processes: ';
            ModuleModelObserver::observe(OspfInstance::class);

            // Pull data from device
            $ospf_instances_poll = SnmpQuery::context($context_name)
                ->hideMib()->enumStrings()
                ->walk('OSPF-MIB::ospfGeneralGroup')->valuesByIndex();

            $ospf_instances = new Collection();
            foreach ($ospf_instances_poll as $ospf_instance_id => $ospf_entry) {
                if (empty($ospf_entry['ospfRouterId'])) {
                    continue; // skip invalid data
                }
                foreach (['ospfRxNewLsas', 'ospfOriginateNewLsas', 'ospfAreaBdrRtrStatus', 'ospfTOSSupport', 'ospfExternLsaCksumSum', 'ospfExternLsaCount', 'ospfASBdrRtrStatus', 'ospfVersionNumber', 'ospfAdminStat'] as $column) {
                    if (! array_key_exists($column, $ospf_entry) || is_null($ospf_entry[$column])) {
                        continue 2; // This column must exist and not be null
                    }
                }

                $instance = OspfInstance::updateOrCreate([
                    'device_id' => $os->getDeviceId(),
                    'ospf_instance_id' => $ospf_instance_id,
                    'context_name' => $context_name,
                ], $ospf_entry);

                $ospf_instances->push($instance);
            }

            // cleanup
            $os->getDevice()->ospfInstances()
                ->where('context_name', $context_name)
                ->whereNotIn('id', $ospf_instances->pluck('id'))->delete();

            $instance_count = $ospf_instances->count();
            echo $instance_count;
            if ($instance_count == 0) {
                // if there are no instances, don't check for areas, neighbors, and ports
                return;
            }

            echo ' Areas: ';
            ModuleModelObserver::observe(OspfArea::class);

            // Pull data from device
            $ospf_areas = SnmpQuery::context($context_name)
                ->hideMib()->enumStrings()
                ->walk('OSPF-MIB::ospfAreaTable')
                ->mapTable(function ($ospf_area, $ospf_area_id) use ($context_name, $os) {
                    return OspfArea::updateOrCreate([
                        'device_id' => $os->getDeviceId(),
                        'ospfAreaId' => $ospf_area_id,
                        'context_name' => $context_name,
                    ], $ospf_area);
                });

            // cleanup
            $os->getDevice()->ospfAreas()
                ->where('context_name', $context_name)
                ->whereNotIn('id', $ospf_areas->pluck('id'))->delete();

            echo $ospf_areas->count();

            echo ' Ports: ';
            ModuleModelObserver::observe(OspfPort::class);

            // Pull data from device
            $ospf_ports = SnmpQuery::context($context_name)
                ->hideMib()->enumStrings()
                ->walk('OSPF-MIB::ospfIfTable')
                ->mapTable(function ($ospf_port, $ip, $ifIndex) use ($context_name, $os) {
                    // find port_id
                    $ospf_port['port_id'] = (int) $os->getDevice()->ports()->where('ifIndex', $ifIndex)->value('port_id');
                    if ($ospf_port['port_id'] == 0) {
                        $ospf_port['port_id'] = (int) $os->getDevice()->ipv4()
                            ->where('ipv4_address', $ip)
                            ->where('context_name', $context_name)
                            ->value('ipv4_addresses.port_id');
                    }

                    return OspfPort::updateOrCreate([
                        'device_id' => $os->getDeviceId(),
                        'ospf_port_id' => "$ip.$ifIndex",
                        'context_name' => $context_name,
                    ], $ospf_port);
                });

            // cleanup
            $os->getDevice()->ospfPorts()
                ->where('context_name', $context_name)
                ->whereNotIn('id', $ospf_ports->pluck('id'))->delete();

            echo $ospf_ports->count();

            echo ' Neighbours: ';
            ModuleModelObserver::observe(OspfNbr::class);

            // Pull data from device
            $ospf_neighbours = SnmpQuery::context($context_name)
                ->hideMib()->enumStrings()
                ->walk('OSPF-MIB::ospfNbrTable')
                ->mapTable(function ($ospf_nbr, $ip, $ifIndex) use ($context_name, $os) {
                    // get neighbor port_id
                    $ospf_nbr['port_id'] = Ipv4Address::query()
                        ->where('ipv4_address', $ip)
                        ->where('context_name', $context_name)
                        ->value('port_id');

                    return OspfNbr::updateOrCreate([
                        'device_id' => $os->getDeviceId(),
                        'ospf_nbr_id' => "$ip.$ifIndex",
                        'context_name' => $context_name,
                    ], $ospf_nbr);
                });

            // cleanup
            $os->getDevice()->ospfNbrs()
                ->where('context_name', $context_name)
                ->whereNotIn('id', $ospf_neighbours->pluck('id'))->delete();

            echo $ospf_neighbours->count();

            echo ' TOS Metrics: ';

            // Pull data from device
            $ospf_ports_by_ip = $ospf_ports->keyBy('ospfIfIpAddress');
            $ospf_tos_metrics = SnmpQuery::context($context_name)
                ->hideMib()->enumStrings()
                ->walk('OSPF-MIB::ospfIfMetricTable')
                ->mapTable(function ($ospf_tos, $ip) use ($ospf_ports_by_ip) {
                    $port = $ospf_ports_by_ip->get($ip);

                    if (! $port) {
                        // didn't find port by IP, try harder
                        $port = $ospf_ports_by_ip->where(fn ($p) => str_starts_with($p->ospf_port_id, $ip))->first();
                    }

                    if ($port) {
                        $port->fill($ospf_tos)->save();
                    } else {
                        \Log::error("No port found when fetching metrics for $ip");
                    }

                    return $port;
                });

            echo $ospf_tos_metrics->count();
            echo PHP_EOL;

            if ($instance_count) {
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

    /**
     * @inheritDoc
     */
    public function cleanup(Device $device): void
    {
        $device->ospfPorts()->delete();
        $device->ospfNbrs()->delete();
        $device->ospfAreas()->delete();
        $device->ospfInstances()->delete();
    }

    /**
     * @inheritDoc
     */
    public function dump(Device $device)
    {
        return [
            'ospf_ports' => $device->ospfPorts()
                ->leftJoin('ports', 'ospf_ports.port_id', 'ports.port_id')
                ->select(['ospf_ports.*', 'ifIndex'])
                ->get()->map->makeHidden(['id', 'device_id', 'port_id']),
            'ospf_instances' => $device->ospfInstances->map->makeHidden(['id', 'device_id']),
            'ospf_areas' => $device->ospfAreas->map->makeHidden(['id', 'device_id']),
            'ospf_nbrs' => $device->ospfNbrs->map->makeHidden(['id', 'device_id']),
        ];
    }
}
