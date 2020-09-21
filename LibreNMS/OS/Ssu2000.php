<?php
/**
 * Ssu2000.php
 *
 *  SSU2000 Hardware discovery
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
 * @author     Craig Harris
 */

namespace LibreNMS\OS;

use App\Models\Device;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\OS;

class Ssu2000 extends OS implements OSDiscovery
{
    public function discoverOS(Device $device): void
    {
        $info = snmp_getnext_multi($this->getDevice(), 'inSerial.1.1 inHwPart.1.1 inSwPart.1.1', '-OQUs', 'SSU2000-MIB');

        $device->version = $info['inHwPart'];
        $device->hardware = 'Comm Processor ' . $info['inSwPart'];
        $device->serial = $info['inSerial'];
    }
}
