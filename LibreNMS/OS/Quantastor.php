<?php
/**
 * Quantastor.php
 *
 * OSNEXUS QuantaStor OS
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
 * @copyright  2020 Cercel Valentin
 * @author     Cercel Valentin <crc@nuamchefazi.ro>
 */

namespace LibreNMS\OS;

use App\Models\Device;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\OS;

class Quantastor extends OS implements OSDiscovery
{
    public function discoverOS(Device $device): void
    {
        $info = snmp_get_multi_oid($this->getDeviceArray(), 'storageSystem-ServiceVersion.0 hwEnclosure-Vendor.0 hwEnclosure-Model.0 storageSystem-SerialNumber.0', '-OQUs', 'QUANTASTOR-SYS-STATS');
        $device->version = $info['storageSystem-ServiceVersion.0'];
        $device->hardware = $info['hwEnclosure-Vendor.0'] . ' ' . $info['hwEnclosure-Model.0'];
        $device->serial = $info['storageSystem-SerialNumber.0'];
    }
}
