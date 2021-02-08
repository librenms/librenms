<?php
/**
 * Zynos.php
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
use LibreNMS\OS\Shared\Zyxel;

class Zynos extends Zyxel implements OSDiscovery
{
    public function discoverOS(Device $device): void
    {
        parent::discoverOS($device); // yaml

        // if not already set, let's fill the gaps
        if (empty($device->hardware)) {
            $device->hardware = $device->sysDescr;
        }

        if (empty($device->serial)) {
            $serial_oids = [
                '.1.3.6.1.4.1.890.1.5.8.20.1.10.0', // ZYXEL-GS4012F-MIB::sysSerialNumber.0
                '.1.3.6.1.4.1.890.1.5.8.47.1.10.0', // ZYXEL-MGS3712-MIB::sysSerialNumber.0
                '.1.3.6.1.4.1.890.1.5.8.55.1.10.0', // ZYXEL-GS2200-24-MIB::sysSerialNumber.0
            ];
            $serials = snmp_get_multi_oid($this->getDeviceArray(), $serial_oids);

            foreach ($serial_oids as $oid) {
                if (! empty($serials[$oid])) {
                    $device->serial = $serials[$oid];
                    break;
                }
            }
        }
    }
}
