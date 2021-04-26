<?php
/*
 * Topvision.php
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
use LibreNMS\RRD\RrdDefinition;

class Topvision extends \LibreNMS\OS implements OSPolling
{
    public function discoverOS(Device $device): void
    {
        parent::discoverOS($device); // yaml
        $device->serial = snmp_getnext($this->getDeviceArray(), '.1.3.6.1.4.1.32285.11.1.1.2.1.1.1.16', '-OQv') ?? null;
        if (empty($device->hardware)) {
            $device->hardware = snmp_getnext($this->getDeviceArray(), '.1.3.6.1.4.1.32285.11.1.1.2.1.1.1.18', '-OQv') ?? null;
        }
    }

    public function pollOS()
    {
        $cmstats = snmp_get_multi_oid($this->getDeviceArray(), ['.1.3.6.1.4.1.32285.11.1.1.2.2.3.1.0', '.1.3.6.1.4.1.32285.11.1.1.2.2.3.6.0', '.1.3.6.1.4.1.32285.11.1.1.2.2.3.5.0']);
        if (is_numeric($cmstats['.1.3.6.1.4.1.32285.11.1.1.2.2.3.1.0'])) {
            $rrd_def = RrdDefinition::make()->addDataset('cmtotal', 'GAUGE', 0);
            $fields = [
                'cmtotal' => $cmstats['.1.3.6.1.4.1.32285.11.1.1.2.2.3.1.0'],
            ];
            $tags = compact('rrd_def');
            data_update($this->getDeviceArray(), 'topvision_cmtotal', $tags, $fields);
            $this->enableGraph('topvision_cmtotal');
        }

        if (is_numeric($cmstats['.1.3.6.1.4.1.32285.11.1.1.2.2.3.6.0'])) {
            $rrd_def = RrdDefinition::make()->addDataset('cmreg', 'GAUGE', 0);
            $fields = [
                'cmreg' => $cmstats['.1.3.6.1.4.1.32285.11.1.1.2.2.3.6.0'],
            ];
            $tags = compact('rrd_def');
            data_update($this->getDeviceArray(), 'topvision_cmreg', $tags, $fields);
            $this->enableGraph('topvision_cmreg');
        }

        if (is_numeric($cmstats['.1.3.6.1.4.1.32285.11.1.1.2.2.3.5.0'])) {
            $rrd_def = RrdDefinition::make()->addDataset('cmoffline', 'GAUGE', 0);
            $fields = [
                'cmoffline' => $cmstats['.1.3.6.1.4.1.32285.11.1.1.2.2.3.5.0'],
            ];
            $tags = compact('rrd_def');
            data_update($this->getDeviceArray(), 'topvision_cmoffline', $tags, $fields);
            $this->enableGraph('topvision_cmoffline');
        }
    }
}
