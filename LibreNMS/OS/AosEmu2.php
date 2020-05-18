<?php
/**
 * AosEmu2.php
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
 * @copyright  2016 Neil Lathwood
 * @author     Neil Lathwood <neil@lathwood.co.uk>
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\OS;

class AosEmu2 extends OS implements OSDiscovery
{
    public function discoverOS(): void
    {
        $aos_emu2_data = snmp_get_multi_oid($this->getDevice(), ['emsIdentSerialNumber.0', 'emsIdentProductNumber.0', 'emsIdentHardwareRev.0', 'emsIdentFirmwareRev.0'], '-OQUs', 'PowerNet-MIB');

        $device = $this->getDeviceModel();
        $device->serial   = $aos_emu2_data['emsIdentSerialNumber.0'];
        $device->hardware = $aos_emu2_data['emsIdentProductNumber.0'] . ' ' . $aos_emu2_data['emsIdentHardwareRev.0'];
        $device->version  = $aos_emu2_data['emsIdentFirmwareRev.0'];
    }
}
