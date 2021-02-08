<?php
/**
 * Iosxr.php
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\Device;
use LibreNMS\Interfaces\Discovery\OSDiscovery;

class Iosxr extends Shared\Cisco implements OSDiscovery
{
    public function discoverOS(Device $device): void
    {
        $device->serial = $this->getMainSerial();

        if (preg_match('/^Cisco IOS XR Software \(Cisco ([^\)]+)\),\s+Version ([^\[]+)\[([^\]]+)\]/', $device->sysDescr, $regexp_result)) {
            $device->hardware = $regexp_result[1];
            $device->features = $regexp_result[3];
            $device->version = $regexp_result[2];
        } elseif (preg_match('/^Cisco IOS XR Software \(([^\)]+)\),\s+Version\s+([^\s]+)/', $device->sysDescr, $regexp_result)) {
            $device->hardware = $regexp_result[1];
            $device->version = $regexp_result[2];
        }

        $oids = ['entPhysicalSoftwareRev.1', 'entPhysicalModelName.8384513', 'entPhysicalModelName.8384518'];
        $data = snmp_get_multi($this->getDeviceArray(), $oids, '-OQUs', 'ENTITY-MIB');

        if (! empty($data[1]['entPhysicalSoftwareRev'])) {
            $device->version = $data[1]['entPhysicalSoftwareRev'];
        }

        if (! empty($data[8384513]['entPhysicalModelName'])) {
            $device->hardware = $data[8384513]['entPhysicalModelName'];
        } elseif (! empty($data[8384518]['entPhysicalModelName'])) {
            $device->hardware = $data[8384518]['entPhysicalModelName'];
        }
    }
}
