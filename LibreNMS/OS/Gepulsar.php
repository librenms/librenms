<?php
/**
 * Gepulsar.php
 *
 * GE Pulsar Controllers
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
 * @author     Craig Harris
 */

namespace LibreNMS\OS;

use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\OS;

class Gepulsar extends OS implements OSDiscovery
{
    public function discoverOS(): void
    {
        $device = $this->getDeviceModel();

        $oids = [
            'serial' => '.1.3.6.1.4.1.10520.2.1.3.2.12.0',
            'version' => '.1.3.6.1.4.1.10520.2.1.3.2.7.0',
            'hardware' => '.1.3.6.1.4.1.10520.2.1.3.2.11.0',
        ];
        $os_data = snmp_get_multi_oid($this->getDevice(), $oids);
        foreach ($oids as $var => $oid) {
            $device->$var = $os_data[$oid];
        }
    }
}
