<?php
/*
 * Stp.php
 *
 * Spanning Tree
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
 * @copyright  2021 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Modules;

use App\Models\Device;
use App\Models\PortStp;
use App\Observers\ModuleModelObserver;
use Illuminate\Support\Facades\Log;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Module;
use LibreNMS\OS;
use LibreNMS\Polling\ModuleStatus;

class Stp implements Module
{
    use SyncsModels;

    /**
     * @inheritDoc
     */
    public function dependencies(): array
    {
        return ['ports', 'vlans'];
    }

    public function shouldDiscover(OS $os, ModuleStatus $status): bool
    {
        return $status->isEnabledAndDeviceUp($os->getDevice());
    }

    public function discover(OS $os): void
    {
        $device = $os->getDevice();

        $instances = $os->discoverStpInstances();
        Log::info('Instances: ');
        ModuleModelObserver::observe(\App\Models\Stp::class);
        $this->syncModels($device, 'stpInstances', $instances);

        $ports = $os->discoverStpPorts($instances);
        Log::info('Ports: ');
        ModuleModelObserver::observe(PortStp::class);
        $this->syncModels($device, 'stpPorts', $ports);
    }

    public function shouldPoll(OS $os, ModuleStatus $status): bool
    {
        return $status->isEnabledAndDeviceUp($os->getDevice());
    }

    public function poll(OS $os, DataStorageInterface $datastore): void
    {
        $device = $os->getDevice();

        Log::info('Instances: ');
        $instances = $device->stpInstances;
        $instances = $os->pollStpInstances($instances);
        ModuleModelObserver::observe(\App\Models\Stp::class);
        $this->syncModels($device, 'stpInstances', $instances);

        Log::info('Ports: ');
        $ports = $device->stpPorts;
        ModuleModelObserver::observe(PortStp::class);
        $this->syncModels($device, 'stpPorts', $ports);
    }

    public function dataExists(Device $device): bool
    {
        return $device->stpInstances()->exists() || $device->stpPorts()->exists();
    }

    public function cleanup(Device $device): int
    {
        $deleted = $device->stpInstances()->delete();
        $deleted += $device->stpPorts()->delete();

        return $deleted;
    }

    /**
     * @inheritDoc
     */
    public function dump(Device $device, string $type): ?array
    {
        return [
            'stp' => $device->stpInstances()->orderBy('bridgeAddress')
                ->get()->map->makeHidden(['stp_id', 'device_id']),
            'ports_stp' => $device->portsStp()->orderBy('port_index')
                ->leftJoin('ports', 'ports_stp.port_id', 'ports.port_id')
                ->select(['ports_stp.*', 'ifIndex'])
                ->get()->map->makeHidden(['port_stp_id', 'device_id', 'port_id']),
        ];
    }
}
