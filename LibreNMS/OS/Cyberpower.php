<?php
/*
 * Cyberpower.php
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

use Illuminate\Support\Str;

class Cyberpower extends \LibreNMS\OS
{
    public function discoverOS(): void
    {
        $device = $this->getDeviceModel();
        $oids = Str::startsWith($device->sysObjectID, '.1.3.6.1.4.1.3808.1.1.1') ? [
            'version' => '.1.3.6.1.4.1.3808.1.1.3.1.3.0',
            'hardware' => '.1.3.6.1.4.1.3808.1.1.3.1.5.0',
            'serial' => '.1.3.6.1.4.1.3808.1.1.3.1.6.0',
        ] : [
            'hardware' => '.1.3.6.1.4.1.3808.1.1.1.1.1.1.0',
            'version' => '.1.3.6.1.4.1.3808.1.1.1.1.2.1.0',
            'serial' => '.1.3.6.1.4.1.3808.1.1.1.1.2.3.0',
        ];
        $data = snmp_get_multi_oid($this->getDevice(), $oids, '-OUQn', 'CPS-MIB');

        $device->hardware = $data[$oids['hardware']] ?? null;
        $device->version = $data[$oids['version']] ?? null;
        $device->serial = $data[$oids['serial']] ?? null;
    }
}
