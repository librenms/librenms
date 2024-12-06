<?php
/*
 * Speedtouch.php
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

class Speedtouch extends OS
{
    public function discoverOS(Device $device): void
    {
        parent::discoverOS($device); // yaml

        // Filthy hack to get software version. may not work on anything but 585v7 :)
        $loop = \SnmpQuery::get('IF-MIB::ifDescr.101')->value();

        if ($loop) {
            preg_match('@([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)@i', $loop, $matches);
            $device->version = $matches[1];
        }
    }
}
