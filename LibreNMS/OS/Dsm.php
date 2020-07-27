<?php
/**
 * Dsm.php
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link       http://librenms.org
 * @copyright  2018 Nick Peelman
 * @copyright  2020 Daniel Baeza
 * @author     Nick Peelman <nick@peelman.us>
 * @author     Daniel Baeza <doctoruve@gmail.com>
 */

namespace LibreNMS\OS;

use Illuminate\Support\Str;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\OS;

class Dsm extends OS implements OSDiscovery
{
    public function discoverOS(): void
    {
        $device = $this->getDeviceModel();
        $tmp_dsm = snmp_get_multi_oid($this->getDevice(), ['modelName.0', 'version.0', 'serialNumber.0'], '-OUQs', 'SYNOLOGY-SYSTEM-MIB');
        $device->hardware = $tmp_dsm['modelName.0'];
        $device->version = Str::replaceFirst('DSM ', '', $tmp_dsm['version.0']);
        $device->serial = $tmp_dsm['serialNumber.0'];
        unset($tmp_dsm);
    }
}
