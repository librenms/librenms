<?php
/**
 * Eatonpdu.php
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

class Eatonpdu extends \LibreNMS\OS
{
    public function discoverOS(): void
    {
        $device = $this->getDeviceModel();

        $data = snmp_get_multi($this->getDevice(), [
            'partNumber.0',
            'objectName.0',
            'firmwareVersion.0',
            'serialNumber.0',
        ], '-OQUs', 'EATON-EPDU-MIB:PDU-MIB');

        $device->hardware = trim($data[0]['partNumber'] . ' ' . $data[0]['objectName']) ?: null;
        $device->version = $data[0]['firmwareVersion'] ?? null;
        $device->serial = $data[0]['serialNumber'] ?? null;
    }
}
