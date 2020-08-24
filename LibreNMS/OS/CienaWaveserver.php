<?php
/**
 * CienaWaveserver.php
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
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\OS;

class CienaWaveserver extends OS implements OSDiscovery
{
    public function discoverOS(): void
    {
        $device = $this->getDeviceModel();
        $oids = [
            'serial' => '.1.3.6.1.2.1.47.1.1.1.1.11.1',
            'version' => '.1.3.6.1.4.1.1271.3.4.14.3.1.5.0',
            'hardware' => '.1.3.6.1.4.1.1271.3.4.6.3.1.3.0'
        ];
        $os_data = snmp_get_multi_oid($this->getDevice(), $oids);
        foreach ($oids as $var => $oid) {
            $device->$var = $os_data[$oid] ?? null;
        }
    }
}
