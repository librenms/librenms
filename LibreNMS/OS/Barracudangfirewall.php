<?php
/**
 * Barracudangfirewall.php
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
use LibreNMS\OS;
use LibreNMS\RRD\RrdDefinition;

class Barracudangfirewall extends OS implements OSDiscovery, OSPolling
{
    public function discoverOS(Device $device): void
    {
        if ($device->sysObjectID == '.1.3.6.1.4.1.10704.1.10') {
            $device->hardware = $device->sysName;
        }
    }

    public function pollOS()
    {
        // TODO move to count sensor
        $sessions = snmp_get($this->getDeviceArray(), 'firewallSessions64.8.102.119.83.116.97.116.115.0', '-OQv', 'PHION-MIB');

        if (is_numeric($sessions)) {
            $rrd_def = RrdDefinition::make()->addDataset('fw_sessions', 'GAUGE', 0);
            $fields = ['fw_sessions' => $sessions];

            $tags = compact('rrd_def');
            app('Datastore')->put($this->getDeviceArray(), 'barracuda_firewall_sessions', $tags, $fields);
            $this->enableGraph('barracuda_firewall_sessions');
        }
    }
}
