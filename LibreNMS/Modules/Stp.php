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

use App\Models\PortStp;
use App\Observers\ModuleModelObserver;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Interfaces\Discovery\StpInstanceDiscovery;
use LibreNMS\Interfaces\Discovery\StpPortDiscovery;
use LibreNMS\Interfaces\Module;
use LibreNMS\Interfaces\Polling\StpInstancePolling;
use LibreNMS\Interfaces\Polling\StpPortPolling;
use LibreNMS\OS;

class Stp implements Module
{
    use SyncsModels;

    public function discover(OS $os): void
    {
        $device = $os->getDevice();

        if ($os instanceof StpInstanceDiscovery) {
            echo 'Instances: ';
            $instances = $os->discoverStpInstances();
            ModuleModelObserver::observe(\App\Models\Stp::class);
            $this->syncModels($device, 'stpInstances', $instances);

            if ($os instanceof StpPortDiscovery) {
                echo "\nPorts: ";
                $ports = $os->discoverStpPorts($instances);
                ModuleModelObserver::observe(PortStp::class);
                $this->syncModels($device, 'stpPorts', $ports);
            }
            echo PHP_EOL;
        }
    }

    public function poll(OS $os): void
    {
        $device = $os->getDevice();

        if ($os instanceof StpInstancePolling) {
            echo 'Instances: ';
            $instances = $device->stpInstances;
            $instances = $os->pollStpInstances($instances);
            ModuleModelObserver::observe(\App\Models\Stp::class);
            $this->syncModels($device, 'stpInstances', $instances);
        }

        if ($os instanceof StpPortPolling) {
            echo "\nPorts: ";
            $ports = $device->stpPorts;
            ModuleModelObserver::observe(PortStp::class);
            $this->syncModels($device, 'stpPorts', $ports);
        }
    }

    public function cleanup(OS $os): void
    {
        $os->getDevice()->stpInstances()->delete();
        $os->getDevice()->stpPorts()->delete();
    }

    /**
     * designated root is stored in format 2 octet bridge priority + MAC address, so we need to normalize it
     */
    public function rootToMac(string $root): string
    {
        $dr = str_replace(['.', ' ', ':', '-'], '', strtolower($root));

        return substr($dr, -12); //remove first two octets
    }

    public function designatedPort(string $dp): int
    {
        if (preg_match('/-(\d+)/', $dp, $matches)) {
            // Syntax with "priority" dash "portID" like so : 32768-54, both in decimal
            return (int) $matches[1];
        }

        // Port saved in format priority+port (ieee 802.1d-1998: clause 8.5.5.1)
        $dp = substr($dp, -2); //discard the first octet (priority part)

        return (int) hexdec($dp);
    }
}
