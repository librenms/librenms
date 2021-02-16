<?php
/*
 * Xerox.php
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
 * @package    LibreNMS
 * @link       https://www.librenms.org
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\Device;

class Xerox extends Shared\Printer
{
    public function discoverOS(Device $device): void
    {
        parent::discoverOS($device); // yaml

        $info = $this->parseDeviceId(snmp_get($this->getDeviceArray(), '.1.3.6.1.4.1.253.8.51.1.2.1.20.1', '-OQv'));
        $device->hardware = $info['MDL'] ?? $info['DES'] ?? $device->hardware;
    }
}
