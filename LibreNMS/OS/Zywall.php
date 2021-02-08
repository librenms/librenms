<?php
/**
 * Zywall.php
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
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\Device;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\Interfaces\Polling\OSPolling;
use LibreNMS\OS\Shared\Zyxel;
use LibreNMS\RRD\RrdDefinition;

class Zywall extends Zyxel implements OSDiscovery, OSPolling
{
    public function discoverOS(Device $device): void
    {
        parent::discoverOS($device); // yaml

        $device->hardware = $device->hardware ?: $device->sysDescr;
        // ZYXEL-ES-COMMON::sysSwVersionString.0
        $pos = strpos($device->version, 'ITS');
        if ($pos) {
            $device->version = substr($device->version, 0, $pos);
        }
    }

    public function pollOS()
    {
        $sessions = snmp_get($this->getDeviceArray(), '.1.3.6.1.4.1.890.1.6.22.1.6.0', '-Ovq');
        if (is_numeric($sessions)) {
            $rrd_def = RrdDefinition::make()->addDataset('sessions', 'GAUGE', 0, 3000000);
            $fields = [
                'sessions' => $sessions,
            ];
            $tags = compact('rrd_def');
            data_update($this->getDeviceArray(), 'zywall-sessions', $tags, $fields);
            $this->enableGraph('zywall_sessions');
        }
    }
}
