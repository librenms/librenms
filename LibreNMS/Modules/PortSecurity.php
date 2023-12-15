<?php
/*
 * PortSecurity.php
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
 * @copyright  2023 Michael Adams
 * @author     Michael Adams <mradams@ilstu.edu>
 */

namespace LibreNMS\Modules;

use App\Models\Device;
use App\Observers\ModuleModelObserver;
use LibreNMS\Config;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Interfaces\Polling\PortSecurityPolling;
use LibreNMS\OS;
use LibreNMS\Polling\ModuleStatus;

class PortSecurity implements \LibreNMS\Interfaces\Module
{
    use SyncsModels;

    /**
     * @inheritDoc
     */
    public function dependencies(): array
    {
        return ['devices', 'ports'];
    }

    public function shouldDiscover(OS $os, ModuleStatus $status): bool
    {
        // libvirt does not use snmp, only ssh tunnels
        return $status->isEnabledAndDeviceUp($os->getDevice(), check_snmp: ! Config::get('enable_libvirt')) && $os instanceof PortSecurityDiscovery;
    }

    /**
     * @inheritDoc
     */
    public function discover(OS $os): void
    {
        if ($os instanceof PortSecurityDiscovery) {
            $cps = $os->discoverPortSecurity();

            ModuleModelObserver::observe(\App\Models\PortSecurity::class);
            $this->syncModels($os->getDevice(), 'PortSecurity', $cps);
        }
        echo PHP_EOL;
    }

    public function shouldPoll(OS $os, ModuleStatus $status): bool
    {
        return $status->isEnabledAndDeviceUp($os->getDevice()) && $os instanceof PortSecurityPolling;
    }

    /**
     * @inheritDoc
     */
    public function poll(OS $os, DataStorageInterface $datastore): void
    {
        if ($os->getDevice()->PortSecurity->isEmpty()) {
            return;
        }

        if ($os instanceof PortSecurityPolling) {
            $cps = $os->pollPortSecurity($os->getDevice()->PortSecurity);

            ModuleModelObserver::observe(\App\Models\PortSecurity::class);
            $this->syncModels($os->getDevice(), 'PortSecurity', $cps);

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
        $device->PortSecurity()->delete();
    }

    /**
     * @inheritDoc
     */
    public function dump(Device $device)
    {
        return [
            'PortSecurity' => $device->PortSecurity()->orderBy('ifIndex')
                ->get()->map->makeHidden(['id', 'device_id']),
        ];
    }
}
