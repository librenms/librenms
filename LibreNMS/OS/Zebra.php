<?php
/*
 * Zebra.php
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
use Illuminate\Support\Str;
use LibreNMS\OS;

class Zebra extends OS
{
    public function discoverOS(Device $device): void
    {
        $device->features = Str::contains($device->sysDescr, 'ireless') ? 'wireless' : 'wired';

        // clarified on 140Xi4
        $data =  snmp_get_multi_oid($this->getDeviceArray(),
            ['.1.3.6.1.4.1.10642.1.3.0',
            '.1.3.6.1.4.1.10642.1.2.0',
            '.1.3.6.1.4.1.10642.1.1.0',
            '.1.3.6.1.4.1.10642.1.4.0']
        );
        $device->purpose = $data['.1.3.6.1.4.1.10642.1.3.0'] ?? null;
        $device->version  = $data['.1.3.6.1.4.1.10642.1.2.0'] ?? null;
        $device->hardware = $data['.1.3.6.1.4.1.10642.1.1.0'] ?? null;
        $device->sysName  = $data['.1.3.6.1.4.1.10642.1.4.0'] ?? null;
    }
}
