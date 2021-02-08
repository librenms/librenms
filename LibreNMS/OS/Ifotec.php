<?php
/**
 * ifotec.inc.php
 *
 * LibreNMS os poller module for Ifotec devices
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
 * @copyright  LibreNMS contributors
 * @author     Cedric MARMONIER
 */

namespace LibreNMS\OS;

use App\Models\Device;
use Illuminate\Support\Str;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\OS;

class Ifotec extends OS implements OSDiscovery
{
    public function discoverOS(Device $device): void
    {
        if (Str::startsWith($device->sysObjectID, '.1.3.6.1.4.1.21362.100.')) {
            $ifoSysProductIndex = snmp_get($this->getDeviceArray(), 'ifoSysProductIndex.0', '-Oqv', 'IFOTEC-SMI');

            if ($ifoSysProductIndex !== null) {
                $oids = [
                    'ifoSysSerialNumber.' . $ifoSysProductIndex,
                    'ifoSysFirmware.' . $ifoSysProductIndex,
                    'ifoSysBootloader.' . $ifoSysProductIndex,
                ];
                $data = snmp_get_multi($this->getDeviceArray(), $oids, ['-OQUs'], 'IFOTEC-SMI');

                $device->version = $data[1]['ifoSysFirmware'] . ' (Bootloader ' . $data[1]['ifoSysBootloader'] . ')';
                $device->serial = $data[1]['ifoSysSerialNumber'];
            }
        }

        // sysDecr struct = (<product_reference> . ' : ' . <product_description>) OR (<product_reference>)
        [$device->hardware] = explode(' : ', $device->sysDescr, 2);
    }
}
