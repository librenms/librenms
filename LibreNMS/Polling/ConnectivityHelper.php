<?php

/*
 * ConnectivityHelper.php
 *
 * Helper to check the connectivity to a device and optionally save metrics about that connectivity
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
 * @copyright  2021 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Polling;

use App\Facades\LibrenmsConfig;
use App\Models\Device;

class ConnectivityHelper
{
    public static function snmpIsAllowed(Device $device): bool
    {
        return $device->snmp_disable === false;
    }

    public static function pingIsAllowed(Device $device): bool
    {
        return LibrenmsConfig::get('icmp_check') && ! ($device->exists && $device->getAttrib('override_icmp_disable') === 'true');
    }
}
