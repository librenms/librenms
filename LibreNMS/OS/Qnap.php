<?php
/**
 * Qnap.php
 *
 * QNAP Turbo NAS OS
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
 * @copyright  2020 Daniel Baeza
 * @author     Daniel Baeza <doctoruve@gmail.com>
 */

namespace LibreNMS\OS;

use Illuminate\Support\Str;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\OS;

class Qnap extends OS implements OSDiscovery
{
    public function discoverOS(): void
    {
        $device = $this->getDeviceModel();
        $info = snmp_getnext_multi($this->getDevice(), 'enclosureModel enclosureSerialNum entPhysicalFirmwareRev', '-OQUs', 'NAS-MIB:ENTITY-MIB');
        $device->version = Str::replaceFirst('\"', '', $info['entPhysicalFirmwareRev']);
        $device->hardware = $info['enclosureModel'];
        $device->serial = $info['enclosureSerialNum'];
    }
}
