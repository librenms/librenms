<?php
/**
 * ApcMgeups.php
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
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\OS;

class ApcMgeups extends OS implements OSDiscovery
{
    public function discoverOS(): void
    {
        // MG-SNMP-UPS-MIB::upsmgIdentFamilyName.0 = STRING: "PULSAR M"
        // MG-SNMP-UPS-MIB::upsmgIdentModelName.0 = STRING: "2200"
        // MG-SNMP-UPS-MIB::upsmgIdentSerialNumber.0 = STRING: "AQ1H01024"

        $oids = ['upsmgIdentFirmwareVersion.0', 'upsmgIdentFamilyName.0', 'upsmgIdentModelName.0', 'upsmgIdentSerialNumber.0'];
        $data = snmp_get_multi($this->getDevice(), $oids, '-OQUs', 'MG-SNMP-UPS-MIB');

        $device = $this->getDeviceModel();
        $device->version = $data[0]['upsmgIdentFirmwareVersion'] ?? null;
        $device->serial = $data[0]['upsmgIdentSerialNumber'] ?? null;
        $device->hardware = $data[0]['upsmgIdentFamilyName'] ?? null;
        if (isset($data[0]['upsmgIdentModelName'])) {
            $device->hardware .= ' ' . $data[0]['upsmgIdentModelName'];
        }
    }
}
