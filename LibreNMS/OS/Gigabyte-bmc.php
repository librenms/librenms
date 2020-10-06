<?php
/**
 * Gigabyte-bmc.php
 *
 * GIGABYTE-BMC devices
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
 * @link       http://librenms.org
 * @copyright  2020 Hans Erasmus
 * @author     Hans Erasmus <erasmushans27@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\Device;
use Illuminate\Support\Str;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\OS;

class gigabyte-bmc extends OS implements OSDiscovery
{
    public function discoverOS(Device $device): void
    {
        $info = snmp_get_multi($this->getDeviceArray(), ['fruPartNumber.0', 'snmpSetSerialNo.0'], '-OQUs', 'GBTBMC-REV3-MIB:SNMPv2-MIB');
        $device->hardware = $info[0]['fruPartNumber'] ?? null;
        $device->serial = $info[0]['snmpSetSerialNo'] ?? null;
    }
}
