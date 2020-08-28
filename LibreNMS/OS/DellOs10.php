<?php
/**
 * DellOs10.php
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

class DellOs10 extends \LibreNMS\OS
{
    public function discoverOS(): void
    {
        $device = $this->getDeviceModel();
        // DELLEMC-OS10-PRODUCTS-MIB
        $dell_os10_hardware = snmp_get_multi($this->getDevice(), ['os10ChassisType.1', 'os10ChassisHwRev.1', 'os10ChassisServiceTag.1', 'os10ChassisExpServiceCode.1', 'os10ChassisProductSN.1'], '-OQUs', 'DELLEMC-OS10-CHASSIS-MIB');

        $device->hardware = $dell_os10_hardware[1]['os10ChassisType'] ?? null;
        $device->version = $dell_os10_hardware[1]['os10ChassisHwRev'] ?? null;
        $device->serial = $dell_os10_hardware[1]['os10ChassisProductSN'] ?? null;
        $device->features = ($dell_os10_hardware[1]['os10ChassisServiceTag'] ?? null) . '/' . ($dell_os10_hardware[1]['os10ChassisExpServiceCode'] ?? null);
    }
}
