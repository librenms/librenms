<?php
/**
 * Avocent.php
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
use Illuminate\Support\Str;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\OS;

class Avocent extends OS implements OSDiscovery
{
    public function discoverOS(Device $device): void
    {
        $avocent_tmp = snmp_get_multi_oid($this->getDeviceArray(), [
            'pmProductModel.0',
            'pmSerialNumber.0',
            'pmFirmwareVersion.0',
        ], '-OUQs', 'PM-MIB');

        $hardware = $avocent_tmp['pmProductModel.0'] ?? null;
        $serial = $avocent_tmp['pmSerialNumber.0'] ?? null;
        $version = $avocent_tmp['pmFirmwareVersion.0'] ?? null;

        if (empty($hardware)) {
            if (Str::startsWith($device->sysObjectID, '.1.3.6.1.4.1.10418.16')) {
                $avocent_oid = '.1.3.6.1.4.1.10418.16.2.1';
            } elseif (Str::startsWith($device->sysObjectID, '.1.3.6.1.4.1.10418.26')) {
                $avocent_oid = '.1.3.6.1.4.1.10418.26.2.1';
            }
            if (isset($avocent_oid)) {
                $avocent_tmp = snmp_get_multi_oid($this->getDeviceArray(), "$avocent_oid.2.0 $avocent_oid.4.0 $avocent_oid.7.0");
                $hardware = explode(' ', $avocent_tmp["$avocent_oid.2.0"] ?? '', 2)[0] ?: null;
                $serial = $avocent_tmp["$avocent_oid.4.0"] ?? null;
                $version = $avocent_tmp["$avocent_oid.7.0"] ?? null;
            }
        }

        $device->hardware = $hardware ?? null;
        $device->serial = $serial ?? null;
        $device->version = $version ?? null;
    }
}
