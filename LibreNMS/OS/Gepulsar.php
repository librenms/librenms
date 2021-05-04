<?php
/**
 * Gepulsar.php
 *
 * GE Pulsar Controllers
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
 * @author     Craig Harris
 */

namespace LibreNMS\OS;

use App\Models\Device;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\OS;

class Gepulsar extends OS implements OSDiscovery
{
    public function discoverOS(Device $device): void
    {
        $info = snmp_getnext_multi($this->getDeviceArray(), ['ne843Ps1Sn', 'ne843Ps1Verw', 'ne843Ps1Brc'], '-OQUs', 'NE843-MIB');
        $device->version = $info['ne843Ps1Verw'];
        $device->hardware = $info['ne843Ps1Brc'];
        $device->serial = $info['ne843Ps1Sn'];
    }
}
