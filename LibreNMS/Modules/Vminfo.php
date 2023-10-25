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
use LibreNMS\Config;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Discovery\VminfoDiscovery;
use LibreNMS\Interfaces\Polling\VminfoPolling;
use LibreNMS\OS;
use LibreNMS\Polling\ModuleStatus;

class Vminfo implements \LibreNMS\Interfaces\Module
{
    use SyncsModels;

    /**
     * @inheritDoc
     */
    public function dependencies(): array
    {
        return [];
    }

    public function shouldDiscover(OS $os, ModuleStatus $status): bool
    {
        // libvirt does not use snmp, only ssh tunnels
        return $status->isEnabledAndDeviceUp($os->getDevice(), check_snmp: ! Config::get('enable_libvirt')) && $os instanceof VminfoDiscovery;
    }

    /**
     * @inheritDoc
     */
    public function discover(OS $os): void
    {
        if ($os instanceof VminfoDiscovery) {
            $vms = $os->discoverVminfo();

            ModuleModelObserver::observe(\App\Models\Vminfo::class);
            $this->syncModels($os->getDevice(), 'vminfo', $vms);
        }
        echo PHP_EOL;
    }

    public function shouldPoll(OS $os, ModuleStatus $status): bool
    {
        return $status->isEnabledAndDeviceUp($os->getDevice()) && $os instanceof VminfoPolling;
    }

    /**
     * @inheritDoc
     */
    public function poll(OS $os, DataStorageInterface $datastore): void
    {
        if ($os->getDevice()->vminfo->isEmpty()) {
            return;
        }

        if ($os instanceof VminfoPolling) {
            $vms = $os->pollVminfo($os->getDevice()->vminfo);

            ModuleModelObserver::observe(\App\Models\Vminfo::class);
            $this->syncModels($os->getDevice(), 'vminfo', $vms);

            return;
        }

        // just run discovery again
        $this->discover($os);
    }

    /**
     * @inheritDoc
     */
    public function cleanup(Device $device): void
    {
        $device->vminfo()->delete();
    }

    /**
     * @inheritDoc
     */
    public function dump(Device $device)
    {
        return [
            'vminfo' => $device->vminfo()->orderBy('vmwVmVMID')
                ->get()->map->makeHidden(['id', 'device_id']),
        ];
    }
}
