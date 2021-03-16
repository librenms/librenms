<?php
/*
 * Schleifenbauer.php
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
 * @package    LibreNMS
 * @link       https://www.librenms.org
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\Device;

class Schleifenbauer extends \LibreNMS\OS
{
    public function discoverOS(Device $device): void
    {
        $master_unit = snmp_get($this->getDeviceArray(), '.1.3.6.1.4.1.31034.12.1.1.1.2.4.1.2.1', '-Oqv');

        $oids = [
            'hardware' => ".1.3.6.1.4.1.31034.12.1.1.2.1.1.1.5.$master_unit",
            'serial' => ".1.3.6.1.4.1.31034.12.1.1.2.1.1.1.6.$master_unit",
            'firmware' => ".1.3.6.1.4.1.31034.12.1.1.2.1.1.1.2.$master_unit",
            'build' => ".1.3.6.1.4.1.31034.12.1.1.2.1.1.1.3.$master_unit",
        ];

        $data = snmp_get_multi_oid($this->getDeviceArray(), $oids);

        $device->hardware = $data[$oids['hardware']] ?? null;
        $device->serial = $data[$oids['serial']] ?? null;
        $device->version = $data[$oids['firmware']] ?? null;
        if (! empty($data[$oids['build']])) {
            $device->version = trim("$device->version ({$data[$oids['build']]})");
        }
    }
}
