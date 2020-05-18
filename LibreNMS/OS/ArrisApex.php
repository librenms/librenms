<?php
/**
 * ArrisApex.php
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

class ArrisApex extends OS implements OSDiscovery
{
    public function discoverOS(): void
    {
        $device = $this->getDeviceModel();
        $arrisapex_data = snmp_get_multi_oid($this->getDevice(), ['.1.3.6.1.4.1.1166.7.1.7.0', '.1.3.6.1.4.1.1166.7.1.1.0', '.1.3.6.1.4.1.1166.7.1.15.0']);
        $device->version  = $arrisapex_data['.1.3.6.1.4.1.1166.7.1.7.0'] ?? null;
        $device->serial   = $arrisapex_data['.1.3.6.1.4.1.1166.7.1.1.0'] ?? null;
        $device->hardware = $arrisapex_data['.1.3.6.1.4.1.1166.7.1.15.0'] ?? null;
    }
}
