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
 * @copyright  2025 Tony Murray
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
use LibreNMS\DB\SyncsModels;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Module;
use LibreNMS\OS;
use LibreNMS\Polling\ModuleStatus;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Util\IP;
use SnmpQuery;

class Ospfv3 implements Module
{
    use SyncsModels;

    /**
     * @inheritDoc
     */
    public function dependencies(): array
    {
        return ['ports'];
    }

    public function shouldDiscover(OS $os, ModuleStatus $status): bool
    {
        return $status->isEnabledAndDeviceUp($os->getDevice());
    }

    /**
     * @inheritDoc
     */
    public function discover(OS $os): void
    {
        Log::info("Processes: ");
        $instances = new Collection;
        foreach ($os->getDevice()->getVrfContexts() as $context_name) {
            // Check for instance data
            $ospf_group = SnmpQuery::context($context_name)
                ->hideMib()->enumStrings()
                ->walk('OSPFV3-MIB::ospfv3GeneralGroup')->valuesByIndex()[0] ?? [];

            if (empty($ospf_group['ospfv3RouterId'])) {
                continue; // invalid data, try next vrf
            }

            // record instance data
            $ospf_group['router_id'] = long2ip($ospf_group['ospfv3RouterId']);
            $instances->push(Ospfv3Instance::updateOrCreate([
                'device_id' => $os->getDeviceId(),
                'context_name' => $context_name,
            ], $ospf_group));
        }

        if ($instances->isEmpty()) {
            return;
        }

        ModuleModelObserver::observe(Ospfv3Instance::class);
        $this->syncModels($os->getDevice(), 'ospfv3Instances', $instances);
        Log::info(' (Total processes: ' . $instances->count() . ')');

        Log::info('Areas: ');
        $ospf_areas = $instances->map(function (Ospfv3Instance $instance) {
            return SnmpQuery::context($instance->context_name)
                ->hideMib()->enumStrings()
                ->walk('OSPFV3-MIB::ospfv3AreaTable')
                ->mapTable(function ($ospf_area, $ospfv3AreaId) use ($instance) {
                    return $this->createArea($ospfv3AreaId, $instance, $ospf_area);
                });
        })->flatten(); // flatten one level

        ModuleModelObserver::observe(Ospfv3Area::class);
        $ospf_areas = $this->syncModels($os->getDevice(), 'ospfv3Areas', $ospf_areas);
        Log::info(' (Total areas: ' . $ospf_areas->count() . ')');

        Log::info('Ports: ');
        $ospf_ports = $instances->map(function (Ospfv3Instance $instance) use ($ospf_areas) {
            return SnmpQuery::context($instance->context_name)
                ->hideMib()->enumStrings()
                ->walk('OSPFV3-MIB::ospfv3IfTable')
                ->mapTable(function ($ospf_port, $ospfv3IfIndex, $ospfv3IfInstId) use ($instance, $ospf_areas) {
                    return $this->createPort($ospfv3IfIndex, $ospfv3IfInstId, $instance, $ospf_areas, $ospf_port);
                });
        })->flatten(); // flatten one level

        ModuleModelObserver::observe(Ospfv3Port::class);
        $this->syncModels($os->getDevice(), 'ospfv3Ports', $ospf_ports);
        Log::info(' (Total ports: ' . $ospf_ports->count() . ')');

        Log::info('Neighbors: ');
        $ospf_neighbors = $instances->map(function (Ospfv3Instance $instance) {
            return SnmpQuery::context($instance->context_name)
                ->hideMib()->enumStrings()
                ->walk('OSPFV3-MIB::ospfv3NbrTable')
                ->mapTable(function ($ospf_nbr, $ospfv3NbrIfIndex, $ospfv3NbrIfInstId, $ospfv3NbrRtrId) use ($instance) {
                    return $this->createNeighbor($ospfv3NbrIfIndex, $ospfv3NbrIfInstId, $ospfv3NbrRtrId, $instance, $ospf_nbr);
                });
        })->flatten(); // flatten one level

        ModuleModelObserver::observe(Ospfv3Nbr::class);
        $this->syncModels($os->getDevice(), 'ospfv3Nbrs', $ospf_neighbors);
        Log::info(' (Total neighbors: ' . $ospf_neighbors->count() . ')');
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
        $device = $os->getDevice();
        $instances = $device->ospfv3Instances;

        if ($instances->isEmpty()) {
            return;
        }

        // go through each instance (unique context) and fetch values displayed in ui
        Log::info('Instances: ');
        ModuleModelObserver::observe(Ospfv3Instance::class);
        $instances->each(function (Ospfv3Instance $instance) {
            $instanceValues = SnmpQuery::context($instance->context_name)->enumStrings()->get([
                'OSPFV3-MIB::ospfv3AdminStatus.0',
                'OSPFV3-MIB::ospfv3AreaBdrRtrStatus.0',
                'OSPFV3-MIB::ospfv3ASBdrRtrStatus.0',
            ]);
            $instance->ospfv3AdminStatus = $instanceValues->value('OSPFV3-MIB::ospfv3AdminStatus');
            $instance->ospfv3AreaBdrRtrStatus = $instanceValues->value('OSPFV3-MIB::ospfv3AreaBdrRtrStatus');
            $instance->ospfv3ASBdrRtrStatus = $instanceValues->value('OSPFV3-MIB::ospfv3ASBdrRtrStatus');
            $instance->save();
        });

        // Areas
        $ospf_areas = $instances->map(function (Ospfv3Instance $instance) {
            return SnmpQuery::context($instance->context_name)
                ->hideMib()->walk([
                    'OSPFV3-MIB::ospfv3AreaScopeLsaCount',
                ])->mapTable(function ($ospf_area, $ospfv3AreaId) use ($instance) {
                    // create new without full data
                    return $this->createArea($ospfv3AreaId, $instance, $ospf_area);
                });
        })->flatten();

        // fill data in new areas
        Ospfv3Area::creating([$this, 'fetchAndFillArea']);
        Log::info("\nAreas: ");
        ModuleModelObserver::observe(Ospfv3Area::class);
        $ospf_areas = $this->syncModels($device, 'ospfv3Areas', $ospf_areas);

        // Ports
        $ospf_ports = $instances->map(function (Ospfv3Instance $instance) use ($ospf_areas) {
            return SnmpQuery::context($instance->context_name)
                ->hideMib()->enumStrings()
                ->walk([
                    'OSPFV3-MIB::ospfv3IfAdminStatus',
                    'OSPFV3-MIB::ospfv3IfState',
                    'OSPFV3-MIB::ospfv3IfType',
                    'OSPFV3-MIB::ospfv3IfMetricValue',
                    'OSPFV3-MIB::ospfv3IfAreaId',
                ])->mapTable(function ($ospf_port, $ospfv3IfIndex, $ospfv3IfInstId) use ($instance, $ospf_areas) {
                    return $this->createPort($ospfv3IfIndex, $ospfv3IfInstId, $instance, $ospf_areas, $ospf_port);
                });
        })->flatten();

        Ospfv3Port::creating([$this, 'fetchAndFillPort']);
        Log::info("\nPorts: ");
        ModuleModelObserver::observe(Ospfv3Port::class);
        $this->syncModels($device, 'ospfv3Ports', $ospf_ports);

        // Neighbors
        $ospf_neighbors = $instances->map(function (Ospfv3Instance $instance) {
            return SnmpQuery::context($instance->context_name)
                ->hideMib()->enumStrings()
                ->walk([
                    'OSPFV3-MIB::ospfv3NbrState',
                    'OSPFV3-MIB::ospfv3NbrAddress',
                ])->mapTable(function ($ospf_nbr, $ospfv3NbrIfIndex, $ospfv3NbrIfInstId, $ospfv3NbrRtrId) use ($instance) {
                    return $this->createNeighbor($ospfv3NbrIfIndex, $ospfv3NbrIfInstId, $ospfv3NbrRtrId, $instance, $ospf_nbr);
                });
        })->flatten();

        Ospfv3Nbr::creating([$this, 'fetchAndFillNeighbor']);
        Log::info("\nNeighbors: ");
        ModuleModelObserver::observe(Ospfv3Nbr::class);
        $this->syncModels($device, 'ospfv3Nbrs', $ospf_neighbors);

        // Create device-wide statistics RRD
        $rrd_def = RrdDefinition::make()
            ->addDataset('instances', 'GAUGE', 0, 1000000)
            ->addDataset('areas', 'GAUGE', 0, 1000000)
            ->addDataset('ports', 'GAUGE', 0, 1000000)
            ->addDataset('neighbours', 'GAUGE', 0, 1000000);

        $fields = [
            'instances' => $instances->count(),
            'areas' => $ospf_areas->count(),
            'ports' => $ospf_ports->count(),
            'neighbours' => $ospf_neighbors->count(),
        ];

        $datastore->put($os->getDeviceArray(), 'ospf-statistics', ['rrd_def' => $rrd_def], $fields);
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
                ->orderBy('ospfv3IfInstId')->orderBy('ospfv3IfIndex')->orderBy('context_name')
                ->get()->map->makeHidden(['id', 'device_id', 'port_id']),
            'ospfv3_instances' => $device->ospfv3Instances()
                ->orderBy('context_name')
                ->get()->map->makeHidden(['id', 'device_id']),
            'ospfv3_areas' => $device->ospfv3Areas()
                ->orderBy('ospfv3AreaId')->orderBy('context_name')
                ->get()->map->makeHidden(['id', 'device_id']),
            'ospfv3_nbrs' => $device->ospfv3Nbrs()
                ->orderBy('ospfv3NbrIfIndex')->orderBy('ospfv3NbrIfInstId')
                ->orderBy('ospfv3NbrRtrId')->orderBy('context_name')
                ->get()->map->makeHidden(['id', 'device_id']),
        ];
    }

    protected function parseNeighborAddress(array $ospf_nbr): IP|string
    {
        if (empty($ospf_nbr['ospfv3NbrAddress'])) {
            return '';
        }

        $ip_raw = $ospf_nbr['ospfv3NbrAddress'];

        return IP::fromHexString($ip_raw, true)
            ?? IP::parse($ip_raw, true)
            ?? $ip_raw;
    }

    /**
     * create a new area model if $data is null fetch values with individual gets
     */
    private function createArea(int $ospfv3AreaId, Ospfv3Instance $instance, array $data): Ospfv3Area
    {
        $ospf_area = new Ospfv3Area($data);
        $ospf_area->device_id = $instance->device_id;
        $ospf_area->ospfv3_instance_id = $instance->id;
        $ospf_area->context_name = $instance->context_name;
        $ospf_area->ospfv3AreaId = $ospfv3AreaId;

        return $ospf_area;
    }

    private function createPort(int $ospfv3IfIndex, int $ospfv3IfInstId, Ospfv3Instance $instance, Collection $ospf_areas, array $data): Ospfv3Port
    {
        $ospf_port = new Ospfv3Port($data);
        $ospf_port->ospfv3IfIndex = $ospfv3IfIndex;
        $ospf_port->ospfv3IfInstId = $ospfv3IfInstId;
        $ospf_port->ospfv3_instance_id = $instance->id;
        $ospf_port->device_id = $instance->device_id;
        $ospf_port->context_name = $instance->context_name;

        // FIXME not on polling
        $ospf_port->port_id = (int)PortCache::getIdFromIfIndex($ospfv3IfIndex, $instance->device_id);
        if (array_key_exists('ospfv3IfDesignatedRouter', $data)) {
            $ospf_port->ospfv3IfDesignatedRouter = long2ip($data['ospfv3IfDesignatedRouter']);
        }
        if (array_key_exists('ospfv3IfBackupDesignatedRouter', $data)) {
            $ospf_port->ospfv3IfBackupDesignatedRouter = long2ip($data['ospfv3IfBackupDesignatedRouter']);
        }
        $ospf_port->ospfv3_area_id = $ospf_areas
            ->firstWhere(function (Ospfv3Area $area) use ($ospf_port) {
                return $area->context_name == $ospf_port->context_name
                    && $area->ospfv3AreaId == $ospf_port->ospfv3IfAreaId;
            })?->id;

        return $ospf_port;
    }

    private function createNeighbor(int $ospfv3NbrIfIndex, int $ospfv3NbrIfInstId, int $ospfv3NbrRtrId, Ospfv3Instance $instance, array $data): Ospfv3Nbr
    {
        $ospf_nbr = new Ospfv3Nbr($data);
        $ospf_nbr->device_id = $instance->device_id;
        $ospf_nbr->context_name = $instance->context_name;
        $ospf_nbr->ospfv3NbrIfIndex = $ospfv3NbrIfIndex;
        $ospf_nbr->ospfv3NbrIfInstId = $ospfv3NbrIfInstId;
        $ospf_nbr->ospfv3NbrRtrId = $ospfv3NbrRtrId;
        $ospf_nbr->router_id = long2ip($ospfv3NbrRtrId);
        $ospf_nbr->ospfv3_instance_id = $instance->id;

        $ospfv3NbrAddress = $this->parseNeighborAddress($data);
        // FIXME not on polling
        // Needs searching by Link-Local addressing, but those do not appear to be indexed.
        $ospf_nbr->port_id = PortCache::getIdFromIp($ospfv3NbrAddress, $instance->context_name); // search all devices
        $ospf_nbr->ospfv3NbrAddress = (string) $ospfv3NbrAddress;

        return $ospf_nbr;
    }

    public function fetchAndFillArea(Ospfv3Area $area): void
    {
        $area->fill(SnmpQuery::context($area->context_name)
            ->hideMib()->enumStrings()->get([
                'OSPFV3-MIB::ospfv3AreaImportAsExtern.' . $area->ospfv3AreaId,
                'OSPFV3-MIB::ospfv3AreaSpfRuns.' . $area->ospfv3AreaId,
                'OSPFV3-MIB::ospfv3AreaBdrRtrCount.' . $area->ospfv3AreaId,
                'OSPFV3-MIB::ospfv3AreaAsBdrRtrCount.' . $area->ospfv3AreaId,
                'OSPFV3-MIB::ospfv3AreaScopeLsaCount.' . $area->ospfv3AreaId,
                'OSPFV3-MIB::ospfv3AreaScopeLsaCksumSum.' . $area->ospfv3AreaId,
                'OSPFV3-MIB::ospfv3AreaSummary.' . $area->ospfv3AreaId,
                'OSPFV3-MIB::ospfv3AreaStubMetric.' . $area->ospfv3AreaId,
                'OSPFV3-MIB::ospfv3AreaStubMetricType.' . $area->ospfv3AreaId,
                'OSPFV3-MIB::ospfv3AreaNssaTranslatorRole.' . $area->ospfv3AreaId,
                'OSPFV3-MIB::ospfv3AreaNssaTranslatorState.' . $area->ospfv3AreaId,
                'OSPFV3-MIB::ospfv3AreaNssaTranslatorStabInterval.' . $area->ospfv3AreaId,
                'OSPFV3-MIB::ospfv3AreaNssaTranslatorEvents.' . $area->ospfv3AreaId,
                'OSPFV3-MIB::ospfv3AreaTEEnabled.' . $area->ospfv3AreaId,
            ])->valuesByIndex()[$area->ospfv3AreaId] ?? []);
    }

    public function fetchAndFillPort(Ospfv3Port $port): void
    {
        $ospf_port = SnmpQuery::context($port->context_name)
            ->hideMib()->enumStrings()->get([
                "OSPFV3-MIB::ospfv3IfRtrPriority.$port->ospfv3IfIndex.$port->ospfv3IfInstId",
                "OSPFV3-MIB::ospfv3IfTransitDelay.$port->ospfv3IfIndex.$port->ospfv3IfInstId",
                "OSPFV3-MIB::ospfv3IfRetransInterval.$port->ospfv3IfIndex.$port->ospfv3IfInstId",
                "OSPFV3-MIB::ospfv3IfHelloInterval.$port->ospfv3IfIndex.$port->ospfv3IfInstId",
                "OSPFV3-MIB::ospfv3IfRtrDeadInterval.$port->ospfv3IfIndex.$port->ospfv3IfInstId",
                "OSPFV3-MIB::ospfv3IfPollInterval.$port->ospfv3IfIndex.$port->ospfv3IfInstId",
                "OSPFV3-MIB::ospfv3IfDesignatedRouter.$port->ospfv3IfIndex.$port->ospfv3IfInstId",
                "OSPFV3-MIB::ospfv3IfBackupDesignatedRouter.$port->ospfv3IfIndex.$port->ospfv3IfInstId",
                "OSPFV3-MIB::ospfv3IfEvents.$port->ospfv3IfIndex.$port->ospfv3IfInstId",
                "OSPFV3-MIB::ospfv3IfDemand.$port->ospfv3IfIndex.$port->ospfv3IfInstId",
                "OSPFV3-MIB::ospfv3IfLinkScopeLsaCount.$port->ospfv3IfIndex.$port->ospfv3IfInstId",
                "OSPFV3-MIB::ospfv3IfLinkLsaCksumSum.$port->ospfv3IfIndex.$port->ospfv3IfInstId",
                "OSPFV3-MIB::ospfv3IfLinkLSASuppression.$port->ospfv3IfIndex.$port->ospfv3IfInstId",
                "OSPFV3-MIB::ospfv3IfDemandNbrProbe.$port->ospfv3IfIndex.$port->ospfv3IfInstId",
                "OSPFV3-MIB::ospfv3IfDemandNbrProbeRetransLimit.$port->ospfv3IfIndex.$port->ospfv3IfInstId",
                "OSPFV3-MIB::ospfv3IfDemandNbrProbeInterval.$port->ospfv3IfIndex.$port->ospfv3IfInstId",
                "OSPFV3-MIB::ospfv3IfTEDisabled.$port->ospfv3IfIndex.$port->ospfv3IfInstId",
            ])->valuesByIndex()["$port->ospfv3IfIndex.$port->ospfv3IfInstId"] ?? [];

        $ospf_port['ospfv3IfBackupDesignatedRouter'] ??= '';  // missing on some devices

        $port->fill($ospf_port);
    }

    public function fetchAndFillNeighbor(Ospfv3Nbr $nbr): void
    {
        $nbr->fill(SnmpQuery::context($nbr->context_name)
            ->hideMib()->enumStrings()->get([
                "OSPFV3-MIB::ospfv3NbrAddressType.$nbr->ospfv3NbrIfIndex.$nbr->ospfv3NbrIfInstId.$nbr->ospfv3NbrRtrId",
                "OSPFV3-MIB::ospfv3NbrOptions.$nbr->ospfv3NbrIfIndex.$nbr->ospfv3NbrIfInstId.$nbr->ospfv3NbrRtrId",
                "OSPFV3-MIB::ospfv3NbrPriority.$nbr->ospfv3NbrIfIndex.$nbr->ospfv3NbrIfInstId.$nbr->ospfv3NbrRtrId",
                "OSPFV3-MIB::ospfv3NbrEvents.$nbr->ospfv3NbrIfIndex.$nbr->ospfv3NbrIfInstId.$nbr->ospfv3NbrRtrId",
                "OSPFV3-MIB::ospfv3NbrLsRetransQLen.$nbr->ospfv3NbrIfIndex.$nbr->ospfv3NbrIfInstId.$nbr->ospfv3NbrRtrId",
                "OSPFV3-MIB::ospfv3NbrHelloSuppressed.$nbr->ospfv3NbrIfIndex.$nbr->ospfv3NbrIfInstId.$nbr->ospfv3NbrRtrId",
                "OSPFV3-MIB::ospfv3NbrIfId.$nbr->ospfv3NbrIfIndex.$nbr->ospfv3NbrIfInstId.$nbr->ospfv3NbrRtrId",
                "OSPFV3-MIB::ospfv3NbrRestartHelperStatus.$nbr->ospfv3NbrIfIndex.$nbr->ospfv3NbrIfInstId.$nbr->ospfv3NbrRtrId",
                "OSPFV3-MIB::ospfv3NbrRestartHelperAge.$nbr->ospfv3NbrIfIndex.$nbr->ospfv3NbrIfInstId.$nbr->ospfv3NbrRtrId",
                "OSPFV3-MIB::ospfv3NbrRestartHelperExitReason.$nbr->ospfv3NbrIfIndex.$nbr->ospfv3NbrIfInstId.$nbr->ospfv3NbrRtrId",
            ])->valuesByIndex()["$nbr->ospfv3NbrIfIndex.$nbr->ospfv3NbrIfInstId.$nbr->ospfv3NbrRtrId"] ?? []);
    }
}
