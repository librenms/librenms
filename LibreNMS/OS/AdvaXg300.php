<?php
/**
 * AdvaXg300.php
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
 *
 * @copyright  2021 Heath Barnhart
 * @author     Heath Barnhart <hbarnhart@kanren.net>
 */

namespace LibreNMS\OS;

use App\Models\Device;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\OS;

class AdvaXg300 extends OS implements OSDiscovery
{
    public function discoverOS(Device $device): void
    {

        $serial = snmp_get($this->getDeviceArray(), '.1.3.6.1.4.1.629.1.50.16.4.3.2.0', '-OQv');
        $device->serial = $serial;

        $sysdescr_array = explode(" ", $device->sysDescr);
        $hardware = $sysdescr_array[0] . " " . $sysdescr_array[1] . " " . $sysdescr_array[2];
        $device->hardware = $hardware;

        $version = $sysdescr_array[4] . " " . $sysdescr_array[5];
        $device->version = $version;

    }
}
