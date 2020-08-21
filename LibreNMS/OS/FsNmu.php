<?php
/**
 * FsNmu.php
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
 * @author     Jozef Rebjak <jozefrebjak@icloud.com>
 */

namespace LibreNMS\OS;

use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\OS;

class FsNmu extends OS implements OSDiscovery
{
    public function discoverOS(): void
    {
        $device = $this->getDeviceModel();
        $info = snmp_get_multi($this->getDevice(), ['vSerialNumber.0', 'vDeviceType.0', 'vSoftwareVerion.0'], '-OQUs', 'OAP-C1-OEO');
        $device->serial = $info[0]['vSerialNumber'] ?? null;
        $device->hardware = $info[0]['vDeviceType'] ?? null;
        $device->version = $info[0]['vSoftwareVerion'] ?? null;
    }
}
