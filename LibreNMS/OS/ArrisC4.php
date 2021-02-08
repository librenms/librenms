<?php
/**
 * ArrisC4.php
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2016 Neil Lathwood
 * @author     Neil Lathwood <neil@lathwood.co.uk>
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\Device;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\OS;

class ArrisC4 extends OS implements OSDiscovery
{
    public function discoverOS(Device $device): void
    {
        $device = $this->getDevice();

        preg_match("/CMTS_V([\d.]+),/", $device->sysDescr, $match);
        $device->version = $match[1];

        $data = explode('.', $device->sysObjectID);
        $id = end($data);
        if ($id == '1') {
            $device->hardware = 'C4';
        } elseif ($id == '2') {
            $device->hardware = 'C4c';
        }
    }
}
