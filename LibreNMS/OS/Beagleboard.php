<?php
/**
 * Beagleboard.php
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
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\Device;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\OS;

class Beagleboard extends OS implements
    OSDiscovery
{
    /**
     * Retrieve basic information about the OS / device
     */
    public function discoverOS(Device $device): void
    {
        $oids = ['NET-SNMP-EXTEND-MIB::nsExtendOutputFull."distro"', 'NET-SNMP-EXTEND-MIB::nsExtendOutputFull."hardware"'];
        $info = snmp_get_multi($this->getDeviceArray(), $oids);
        $device->version = str_replace('BeagleBoard.org ', '', $info['"distro"']['nsExtendOutputFull']);
        $device->hardware = $info['"hardware"']['nsExtendOutputFull'];
    }
}
