<?php

/**
 * FdbTable.php
 *
 * FDB table discovery module
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
 * @link       http://librenms.org
 *
 * @copyright  2025 Peca Nesovanovic
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
 */

namespace LibreNMS\Modules;

use App\Facades\PortCache;
use App\Models\Device;
use App\Models\PortsFdb;
use App\Observers\ModuleModelObserver;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Discovery\FdbTableDiscovery;
use LibreNMS\Interfaces\Module;
use LibreNMS\OS;
use LibreNMS\Polling\ModuleStatus;
use LibreNMS\Util\Mac;
use SnmpQuery;

class FdbTable implements Module
{
    use SyncsModels;

    /**
     * @inheritDoc
     */
    public function dependencies(): array
    {
        return ['ports', 'vlans'];
    }

    /**
     * @inheritDoc
     */
    public function shouldDiscover(OS $os, ModuleStatus $status): bool
    {
        return $status->isEnabledAndDeviceUp($os->getDevice());
    }

    /**
     * @inheritDoc
     */
    public function shouldPoll(OS $os, ModuleStatus $status): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function discover(OS $os): void
    {
        //vlans db to array, avoid excessive database query
        $vlansDb = $os->getDevice()->vlans()->get()->keyBy('vlan_vlan')->toArray();

        $fdbt = new Collection;

        if ($os instanceof FdbTableDiscovery) {
            $fdbt = $os->discoverFdbTable();
        }

        if ($fdbt->isEmpty()) {
            $fdbt = $this->discoverFdb($os);
        }

        $fdbt = $fdbt->filter(function ($data) use ($vlansDb) {
            $data->vlan_id = $vlansDb[$data->vlan_id]['vlan_id'] ?? 0; //convert raw_vlan to vlan_id from 'vlans' database
            $data->mac_address = Mac::parse($data->mac_address)->hex(); //convert mac address

            if (empty($data->port_id)) {
                Log::info('Drop MAC: ' . $data->mac_address . ' because of missing port');

                return null;
            }

            if (strlen($data->mac_address) != 12) {
                Log::info('Drop MAC: ' . $data->mac_address . ' because of invalid MAC value');

                return null;
            }

            if (empty($data->vlan_id)) {
                $port = PortCache::get($data->port_id);
                Log::info('Missing VLAN for MAC: ' . $data->mac_address . ' on port ' . $port->ifName);

                //return null;
            }

            return true;
        });

        ModuleModelObserver::observe(PortsFdb::class);
        $this->syncModels($os->getDevice(), 'portsFdb', $fdbt);
    }

    /**
     * @inheritDoc
     */
    public function poll(OS $os, DataStorageInterface $datastore): void
    {
        $this->discover($os);
    }

    /**
     * @inheritDoc
     */
    public function dataExists(Device $device): bool
    {
        return $device->portsFdb()->exists();
    }

    /**
     * @inheritDoc
     */
    public function cleanup(Device $device): int
    {
        return $device->portsFdb()->delete();
    }

    /**
     * @inheritDoc
     */
    public function dump(Device $device, string $type): ?array
    {
        if ($type == 'polling') {
            return null;
        }

        return [
            'ports_fdb' => $device->portsFdb()
                ->leftJoin('ports', 'ports_fdb.port_id', 'ports.port_id')
                ->leftJoin('vlans', 'ports_fdb.vlan_id', 'vlans.vlan_id')
                ->select('ports_fdb.*', 'ports.ifIndex', 'vlans.vlan_vlan')
                ->orderBy('port_id')->orderBy('vlan_vlan')->orderBy('mac_address')
                ->get()->map->makeHidden(['ports_fdb_id', 'port_id', 'device_id', 'created_at', 'updated_at', 'vlan_id']),
        ];
    }

    private function discoverFdb(OS $os): Collection
    {
        $fdbt = new Collection;

        $dot1qTpFdbPort = $os->dot1qTpFdbPort();

        $dot1qVlanFdbId = SnmpQuery::hideMib()->walk('Q-BRIDGE-MIB::dot1qVlanFdbId')->table();
        $dot1qVlanFdbId = $tmp = $dot1qVlanFdbId['dot1qVlanFdbId'] ?? [];
        if (! empty($tmp)) {
            if (! empty(array_shift($tmp))) {
                $dot1qVlanFdbId = array_shift($dot1qVlanFdbId);
            }
            $dot1qVlanFdbId = array_flip($dot1qVlanFdbId);
        }

        foreach ($dot1qTpFdbPort as $vlanIdx => $macData) {
            foreach ($macData as $mac_address => $portIdx) {
                if (is_array($portIdx)) {
                    foreach ($portIdx as $key => $idx) { //multiple port for one mac ???
                        $ifIndex = $os->ifIndexFromBridgePort($idx);
                        $port_id = PortCache::getIdFromIfIndex($ifIndex, $os->getDeviceId()) ?? 0;
                        $fdbt->push(new PortsFdb([
                            'port_id' => $port_id,
                            'mac_address' => $mac_address,
                            'vlan_id' => $dot1qVlanFdbId[$vlanIdx] ?? $vlanIdx,
                        ]));
                    }
                } else {
                    $ifIndex = $os->ifIndexFromBridgePort($portIdx);
                    $port_id = PortCache::getIdFromIfIndex($ifIndex, $os->getDeviceId()) ?? 0;
                    $fdbt->push(new PortsFdb([
                        'port_id' => $port_id,
                        'mac_address' => $mac_address,
                        'vlan_id' => $dot1qVlanFdbId[$vlanIdx] ?? $vlanIdx,
                    ]));
                }
            }
        }

        return $fdbt->filter();
    }
}
