<?php
/**
 * AdvaFsp150.php
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
 * @author     Christoph Zilian <czilian@hotmail.com>
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\OS;

class AdvaFsp150 extends OS implements OSDiscovery
{
    public function discoverOS(): void
    {
        $oids = ['entPhysicalSoftwareRev.1', 'entPhysicalName.1', 'entPhysicalHardwareRev.1', 'entPhysicalSerialNum.1'];
        $data = snmp_get_multi($this->getDevice(), $oids, '-OQUs', 'ENTITY-MIB');

        $device = $this->getDeviceModel();
        $device->version = $data[1]['entPhysicalSoftwareRev'] ?? null;
        $device->serial = $data[1]['entPhysicalSerialNum'] ?? null;
        $device->hardware = $data[1]['entPhysicalName'] ?? null;
        if ($data[1]['entPhysicalHardwareRev']) {
            $device->hardware .= ' V' . $data[1]['entPhysicalHardwareRev'];
        }
    }
}
