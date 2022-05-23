<?php
/**
 * AristaMos.php
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
 *
 * @copyright  2021 Goldman Sachs & Co.
 * @author     Nash Kaminski <Nash.Kaminski@gs.com>
 */

namespace LibreNMS\OS;

use App\Models\Device;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\OS;

class AristaMos extends OS implements OSDiscovery
{
    /**
     * Retrieve basic information about the OS / device
     */
    public function discoverOS(Device $device): void
    {
	if (preg_match('/^Metamako MOS release ([.0-9]+) .*? running on a (.*)$/', $device->sysDescr, $attrs) !== false) {
            $device->serial = snmp_get($this->getDeviceArray(), 'snmpSetSerialNo.0', '-Oqv');
            $device->version = $attrs[1];
            $device->hardware = $attrs[2];
        }
    }
}
