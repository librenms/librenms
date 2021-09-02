<?php
/**
 * EndRun.php
 *
 * EndRun Tempus LX NTP devices
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
 * @copyright  2020 Hans Erasmus
 * @author     Hans Erasmus <erasmushans27@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\Device;
use Illuminate\Support\Str;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\OS;

class Endrun extends OS implements OSDiscovery
{
    public function discoverOS(Device $device): void
    {
        if (Str::contains($device->sysDescr, 'Sonoma')) {
            $info = snmp_get_multi($this->getDeviceArray(), ['gntpVersion.0', 'snmpSetSerialNo.0'], '-OQUs', 'SONOMA-MIB:SNMPv2-MIB');

            // The gntpVersion string output is rather long. Ex. Sonoma_D12 GPS 6010-0065-000 v 3.04 - Sep 24 22:58:19 2019
            preg_match('/(.+) v (.+) - /', $info[0]['gntpVersion'], $matches);
            $device->hardware = $matches[1] ?? null;

            // The EndRun Sonoma D12 does not report a system capability (cdmaVersion) like the Tempus devices.
            $device->serial = $info[0]['snmpSetSerialNo'] ?? null;
        } else {
            $info = snmp_get_multi($this->getDeviceArray(), ['cntpVersion.0', 'cdmaVersion.0', 'snmpSetSerialNo.0'], '-OQUs', 'TEMPUSLXUNISON-MIB:SNMPv2-MIB');
            $device->features = $info[0]['cdmaVersion'] ?? null;
            $device->serial = $info[0]['snmpSetSerialNo'] ?? null;
            // The cntpVersion string output is rather long. Ex. Tempus LX CDMA 6010-0042-000 v 5.70 - Wed Oct 1 04:39:21 UTC 2014
            preg_match('/(.+) v (.+) - /', $info[0]['cntpVersion'], $matches);
            $device->hardware = $matches[1] ?? null;
            $device->version = $matches[2] ?? null;
        }
    }
}
