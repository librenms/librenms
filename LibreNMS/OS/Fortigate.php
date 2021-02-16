<?php
/*
 * Fortigate.php
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
use LibreNMS\Interfaces\Polling\OSPolling;
use LibreNMS\OS\Shared\Fortinet;
use LibreNMS\RRD\RrdDefinition;

class Fortigate extends Fortinet implements OSPolling
{
    public function discoverOS(Device $device): void
    {
        parent::discoverOS($device); // yaml

        $device->hardware = $device->hardware ?: $this->getHardwareName();
    }

    public function pollOS()
    {
        $sessions = snmp_get($this->getDeviceArray(), 'FORTINET-FORTIGATE-MIB::fgSysSesCount.0', '-Ovq');
        if (is_numeric($sessions)) {
            $rrd_def = RrdDefinition::make()->addDataset('sessions', 'GAUGE', 0, 3000000);

            echo "Sessions: $sessions\n";
            $fields = [
                'sessions' => $sessions,
            ];

            $tags = compact('rrd_def');
            app()->make('Datastore')->put($this->getDeviceArray(), 'fortigate_sessions', $tags, $fields);
            $this->enableGraph('fortigate_sessions');
        }

        $cpu_usage = snmp_get($this->getDeviceArray(), 'FORTINET-FORTIGATE-MIB::fgSysCpuUsage.0', '-Ovq');
        if (is_numeric($cpu_usage)) {
            $rrd_def = RrdDefinition::make()->addDataset('LOAD', 'GAUGE', -1, 100);

            echo "CPU: $cpu_usage%\n";
            $fields = [
                'LOAD' => $cpu_usage,
            ];

            $tags = compact('rrd_def');
            app()->make('Datastore')->put($this->getDeviceArray(), 'fortigate_cpu', $tags, $fields);
            $this->enableGraph('fortigate_cpu');
        }
    }
}
