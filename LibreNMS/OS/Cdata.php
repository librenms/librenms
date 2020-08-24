<?php
/**
 * Cdata.php
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

class Cdata extends OS implements OSDiscovery
{
    public function discoverOS(): void
    {
        $device = $this->getDeviceModel();
        $data = snmp_get_multi_oid($this->getDevice(), [
            '.1.3.6.1.4.1.34592.1.3.1.5.2.1.1.4.0',
            '.1.3.6.1.4.1.34592.1.3.1.5.2.1.1.5.0',
            '.1.3.6.1.4.1.17409.2.3.1.3.1.1.9.1.0',
            '.1.3.6.1.4.1.17409.2.3.1.3.1.1.7.1.0',
            '.1.3.6.1.4.1.17409.2.3.1.1.13.0'
        ], '-OQUn');

        $device->version = $data['.1.3.6.1.4.1.17409.2.3.1.3.1.1.9.1.0'] ?? $data['.1.3.6.1.4.1.34592.1.3.1.5.2.1.1.5.0'] ?? null;
        $device->hardware = $data['.1.3.6.1.4.1.17409.2.3.1.3.1.1.7.1.0'] ?? $data['.1.3.6.1.4.1.34592.1.3.1.5.2.1.1.4.0'] ?? null;
        $device->serial = $data['.1.3.6.1.4.1.17409.2.3.1.1.13.0'] ?? null;
    }
}
