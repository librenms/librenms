<?php
/**
 * Aix.php
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
use LibreNMS\OS;

class Aix extends OS implements OSDiscovery
{
    public function discoverOS(Device $device): void
    {
        $aix_descr = explode("\n", $device->sysDescr);
        // AIX standard snmp deamon
        if ($aix_descr[1]) {
            $device->serial = explode('Processor id: ', $aix_descr[1])[1];
            $aix_long_version = explode(' version: ', $aix_descr[2])[1];
            [$device->version, $aix_version_min] = array_map('intval', explode('.', $aix_long_version));
        // AIX net-snmp
        } else {
            [, , $aix_version_min, $device->version, $device->serial] = explode(' ', $aix_descr[0]);
        }
        $device->version .= '.' . $aix_version_min;
    }
}
