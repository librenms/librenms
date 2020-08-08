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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2020 Hans Erasmus
 * @author     Hans Erasmus <erasmushans27@gmail.com>
 */

namespace LibreNMS\OS;

use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\OS;

class Endrun extends OS implements OSDiscovery
{
    public function discoverOS(): void
    {
        $device = $this->getDeviceModel();
        $info = snmp_get_multi($this->getDevice(), ['cntpVersion.0', 'cdmaVersion.0', 'snmpSetSerialNo.0'], '-OQUs', 'TEMPUSLXUNISON-MIB:SNMPv2-MIB');
        $device->features = $info[0]['cdmaVersion'] ?? null;
        $device->serial = $info[0]['snmpSetSerialNo'] ?? null;

        // The cntpVersion string output is rather long. Ex. Tempus LX CDMA 6010-0042-000 v 5.70 - Wed Oct 1 04:39:21 UTC 2014
        preg_match('/(.+) v (.+) - /', $info[0]['cntpVersion'], $matches);
        $device->hardware = $matches[1] ?? null;
        $device->version = $matches[2] ?? null;
    }
}
