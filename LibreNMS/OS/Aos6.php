<?php
/*
 * Aos6.php
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

use Illuminate\Support\Str;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\OS;

class Aos6 extends OS implements OSDiscovery
{
    public function discoverOS(): void
    {
        $device = $this->getDeviceModel();
        if (Str::contains($device->sysDescr, 'Enterprise')) {
            [, , $device->hardware, $device->version] = explode(' ', $device->sysDescr);
        } elseif (Str::startsWith($device->sysObjectID, '.1.3.6.1.4.1.6486.800.1.1.2.1.10')) {
            preg_match('/deviceOmniSwitch(....)(.+)/', snmp_translate($device->sysObjectID, 'ALCATEL-IND1-DEVICES:SNMPv2-MIB'), $model); // deviceOmniSwitch6400P24
            $device->hardware = 'OS' . $model[1] . '-' . $model[2];
            [$device->version,] = explode(' ',  $device->sysDescr);
        } else {
            [, $device->hardware, $device->version] = explode(' ', $device->sysDescr);
        }
    }
}
