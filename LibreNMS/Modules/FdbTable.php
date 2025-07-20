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
use LibreNMS\Interfaces\Module;
use LibreNMS\OS;
use LibreNMS\Polling\ModuleStatus;
use LibreNMS\Util\Mac;

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

    public function shouldPoll(OS $os, ModuleStatus $status): bool
    {
        if (defined('PHPUNIT_RUNNING')) {
            return false; // FIXME improve test suite to skip polling when polling data is null
        }

        return $status->isEnabledAndDeviceUp($os->getDevice());
    }

    /**
     * @inheritDoc
     */
    public function discover(OS $os): void
    {
        //vlans db to array, avoid excessive database query
        $vlansDb = $os->getDevice()->vlans()->get()->keyBy('vlan_vlan')->toArray();

        $fdbt = new Collection;

        $fdbt = $os->discoverFdbTable();

        $fdbt = $fdbt->filter(function ($data) use ($vlansDb) {
            $data->vlan_id = $vlansDb[$data->vlan_id]['vlan_id'] ?? 0; //convert raw_vlan to vlan_id from 'vlans' database
            $data->mac_address = Mac::parse($data->mac_address)->hex(); //convert mac address

            if (strlen($data->mac_address) != 12) {
                Log::debug('Drop MAC: ' . $data->mac_address . ' because of invalid MAC value');

                return false;
            }

            if (empty($data->port_id)) {
                Log::debug('Drop MAC: ' . $data->mac_address . ' because of missing port');

                return false;
            }

            if (empty($data->vlan_id)) {
                $port = PortCache::get($data->port_id);
                Log::info('Missing VLAN for MAC: ' . $data->mac_address . ' on port ' . $port->ifName);

                //return false;
                //will leave this as it is, if we drop vlan_vlan = 0 then we break 'dot1dTpFdbPort' devices which have no vlan data
            }

            return true;
        });

        ModuleModelObserver::observe(PortsFdb::class);
        $this->syncModels($os->getDevice(), 'portsFdb', $fdbt);
        ModuleModelObserver::done();
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
}
