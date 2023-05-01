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
use LibreNMS\DB\SyncsModels;
use LibreNMS\Interfaces\Discovery\VminfoDiscovery;
use LibreNMS\Interfaces\Polling\VminfoPolling;
use LibreNMS\OS;

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

    /**
     * @inheritDoc
     */
    public function poll(OS $os): void
    {
        if ($os->getDevice()->vminfo->isEmpty()) {
            return;
        }

        if ($os instanceof VminfoPolling) {
            $vms = $os->pollVminfo($os->getDevice()->vminfo);

            ModuleModelObserver::observe(Vminfo::class);
            $this->syncModels($os->getDevice(), 'vminfo', $vms);

            return;
        }

        // just run discovery again
        $this->discover($os);
    }

    /**
     * @inheritDoc
     */
    public function discover(OS $os): void
    {
        if ($os instanceof VminfoDiscovery) {
            $vms = $os->discoverVminfo();

            ModuleModelObserver::observe(Vminfo::class);
            $this->syncModels($os->getDevice(), 'vminfo', $vms);
        }
        echo PHP_EOL;
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
