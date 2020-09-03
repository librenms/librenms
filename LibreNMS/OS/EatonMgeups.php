<?php
/**
 * EatonMgeups.php
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

class EatonMgeups extends \LibreNMS\OS
{
    public function discoverOS(): void
    {
        $device = $this->getDeviceModel();

        $data = snmp_get_multi($this->getDevice(), [
            'upsmgIdentFamilyName.0',
            'upsmgIdentModelName.0',
            'upsmgIdentFirmwareVersion.0',
            'upsmgIdentSerialNumber.0',
        ], '-OQUs', 'MG-SNMP-UPS-MIB');

        $device->hardware = trim($data[0]['upsmgIdentFamilyName'] . ' ' . $data[0]['upsmgIdentModelName']);
        $device->version = $data[0]['upsmgIdentFirmwareVersion'] ?? null;
        $device->serial = $data[0]['upsmgIdentSerialNumber'] ?? null;
    }
}
