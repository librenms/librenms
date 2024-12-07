<?php
/*
 * Nitro.php
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
 * @copyright  2021 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\Device;
use LibreNMS\OS;

class Nitro extends OS
{
    public function discoverOS(Device $device): void
    {
        $this->discoverOS($device); // yaml

        if ($device->sysObjectID == '.1.3.6.1.4.1.23128.1000.1.1') {
            $device->features = 'Enterprise Security Manager';
        } elseif ($device->sysObjectID == '.1.3.6.1.4.1.23128.1000.3.1') {
            $device->features = 'Event Receiver';
        } elseif ($device->sysObjectID == '.1.3.6.1.4.1.23128.1000.7.1') {
            $device->features = 'Enterprise Log Manager';
        } elseif ($device->sysObjectID == '.1.3.6.1.4.1.23128.1000.11.1') {
            $device->features = 'Advanced Correlation Engine';
        } else {
            $device->features = 'Unknown';
        }
    }
}
