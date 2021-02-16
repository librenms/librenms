<?php
/*
 * Fortios.php
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

class Fortios extends Fortinet implements OSPolling
{
    public function discoverOS(Device $device): void
    {
        parent::discoverOS($device); // yaml

        $device->hardware = $device->hardware ?: $this->getHardwareName();
        $device->features = snmp_get($this->getDeviceArray(), 'fmDeviceEntMode.1', '-OQv', 'FORTINET-FORTIMANAGER-FORTIANALYZER-MIB') == 'fmg-faz' ? 'with Analyzer features' : null;
    }

    public function pollOS()
    {
        // Log rate only for FortiAnalyzer features enabled FortiManagers
        if ($this->getDevice()->features == 'with Analyzer features') {
            $log_rate = snmp_get($this->getDeviceArray(), '.1.3.6.1.4.1.12356.103.2.1.9.0', '-Ovq');
            $log_rate = str_replace(' logs per second', '', $log_rate);
            $rrd_def = RrdDefinition::make()->addDataset('lograte', 'GAUGE', 0, 100000000);
            $fields = ['lograte' => $log_rate];
            $tags = compact('rrd_def');
            app()->make('Datastore')->put($this->getDeviceArray(), 'fortios_lograte', $tags, $fields);
            $this->enableGraph('fortios_lograte');
        }
    }
}
