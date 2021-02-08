<?php
/**
 * OSDetection.php
 *
 * Used to detect the os of a device.  Primarily this should be done via yaml.
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Interfaces\Discovery;

use App\Models\Device;

interface OSDetection
{
    /**
     * Check if the give device is this OS.
     * $device->sysObjectID and $device->sysDescr will be pre-populated
     * Please avoid additional snmp queries if possible
     *
     * @param Device $device
     * @return bool
     */
    public static function detectOS(Device $device): bool;
}
