<?php
/**
 * Zyxel.php
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
 * @copyright  2016 Neil Lathwood
 * @author     Neil Lathwood <neil@lathwood.co.uk>
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS\Shared;

use App\Models\Device;
use LibreNMS\OS;
use LibreNMS\OS\Traits\YamlOSDiscovery;

class Zyxel extends OS
{
    use YamlOSDiscovery {
        YamlOSDiscovery::discoverOS as discoverYamlOS;
    }

    public function discoverOS(Device $device): void
    {
        // yaml discovery overrides this
        if ($this->hasYamlDiscovery('os')) {
            $this->discoverYamlOS($device);

            return;
        }

        $oids = [
            '.1.3.6.1.4.1.890.1.15.3.1.11.0', // ZYXEL-ES-COMMON::sysProductModel.0
            '.1.3.6.1.4.1.890.1.15.3.1.6.0', // ZYXEL-ES-COMMON::sysSwVersionString.0
            '.1.3.6.1.4.1.890.1.15.3.1.12.0', // ZYXEL-ES-COMMON::sysProductSerialNumber.0
            // ZYXEL-ES-ZyxelAPMgmt::operationMode.0
        ];
        $data = snmp_get_multi_oid($this->getDeviceArray(), $oids, '-OUQnt');

        $device->hardware = $data['.1.3.6.1.4.1.890.1.15.3.1.11.0'];
        [$device->version,] = explode(' | ', $data['.1.3.6.1.4.1.890.1.15.3.1.6.0']);
        $device->serial = $data['.1.3.6.1.4.1.890.1.15.3.1.12.0'];
    }
}
