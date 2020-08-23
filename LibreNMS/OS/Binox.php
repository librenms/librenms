<?php
/**
 * Binox.php
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

class Binox extends OS implements OSDiscovery
{
    public function discoverOS(): void
    {
        $device = $this->getDeviceModel();
        $data = snmp_get_multi_oid($this->getDevice(), [
            '.1.3.6.1.4.1.738.10.111.1.1.1.1.0',
            '.1.3.6.1.4.1.738.10.5.100.1.3.1.0',
            '.1.3.6.1.4.1.738.10.5.100.1.3.4.0'
        ]);

        $device->version = $data['.1.3.6.1.4.1.738.10.111.1.1.1.1.0'] ?? null;
        $device->serial = $data['.1.3.6.1.4.1.738.10.5.100.1.3.1.0'] ?? null;
        $device->hardware = $data['.1.3.6.1.4.1.738.10.5.100.1.3.4.0'] ?? null;
    }
}
