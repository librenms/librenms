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
use Illuminate\Support\Facades\DB;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Discovery\PortSecurityDiscovery;
use LibreNMS\Interfaces\Module;
use LibreNMS\Interfaces\Polling\PortSecurityPolling;
use LibreNMS\OS;
use LibreNMS\Polling\ModuleStatus;

class PortSecurity implements Module
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
        return $status->isEnabledAndDeviceUp($os->getDevice()) && $os instanceof PortSecurityDiscovery;
    }

    /**
     * @inheritDoc
     */
    public function discover(OS $os): void
    {
        $this->poll($os, app('Datastore'));
    }

    public function shouldPoll(OS $os, ModuleStatus $status): bool
    {
        return $status->isEnabledAndDeviceUp($os->getDevice()) && $os instanceof PortSecurityPolling;
    }

    /**
     * Poll data for this module and update the DB
     *
     * @param  \LibreNMS\OS  $os
     */
    public function poll(OS $os, DataStorageInterface $datastore): void
    {
        if ($os->getDevice()->portSecurity->isEmpty()) {
            return;
        }
        if ($os instanceof PortSecurityPolling) {
            $device = $os->getDevice();
            $portsec = $os->pollPortSecurity($os, $device);
            ModuleModelObserver::observe(\App\Models\PortSecurity::class);
            $this->syncModels($device, 'portSecurity', $portsec);
        }
    }

    public function dataExists(Device $device): bool
    {
        return $device->portSecurity()->exists();
    }

    /**
     * @inheritDoc
     */
    public function cleanup(Device $device): int
    {
        return $device->portSecurity()->delete();
    }

    /**
     * @inheritDoc
     */
    public function dump(Device $device, string $type) ?array
    {
        return [
            'PortSecurity' => $device->portSecurity()->orderBy('port_id')
                ->get()->map->makeHidden(['id', 'device_id']),
        ];
    }
}
