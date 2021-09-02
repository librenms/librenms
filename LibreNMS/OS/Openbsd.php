<?php
/*
 * Openbsd.php
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

use LibreNMS\Interfaces\Polling\OSPolling;
use LibreNMS\OS\Shared\Unix;
use LibreNMS\RRD\RrdDefinition;

class Openbsd extends Unix implements OSPolling
{
    public function pollOS()
    {
        $oids = snmp_get_multi($this->getDeviceArray(), ['pfStateCount.0', 'pfStateSearches.0', 'pfStateInserts.0', 'pfStateRemovals.0'], '-OQUs', 'OPENBSD-PF-MIB');

        if (is_numeric($oids[0]['pfStateCount'])) {
            $rrd_def = RrdDefinition::make()->addDataset('states', 'GAUGE', 0);

            $fields = [
                'states' => $oids[0]['pfStateCount'],
            ];

            $tags = compact('rrd_def');
            data_update($this->getDeviceArray(), 'pf_states', $tags, $fields);

            $this->enableGraph('pf_states');
        }

        if (is_numeric($oids[0]['pfStateSearches'])) {
            $rrd_def = RrdDefinition::make()->addDataset('searches', 'COUNTER', 0);

            $fields = [
                'searches' => $oids[0]['pfStateSearches'],
            ];

            $tags = compact('rrd_def');
            data_update($this->getDeviceArray(), 'pf_searches', $tags, $fields);

            $this->enableGraph('pf_searches');
        }

        if (is_numeric($oids[0]['pfStateInserts'])) {
            $rrd_def = RrdDefinition::make()->addDataset('inserts', 'COUNTER', 0);

            $fields = [
                'inserts' => $oids[0]['pfStateInserts'],
            ];

            $tags = compact('rrd_def');
            data_update($this->getDeviceArray(), 'pf_inserts', $tags, $fields);

            $this->enableGraph('pf_inserts');
        }

        if (is_numeric($oids[0]['pfStateCount'])) {
            $rrd_def = RrdDefinition::make()->addDataset('removals', 'COUNTER', 0);

            $fields = [
                'removals' => $oids[0]['pfStateCount'],
            ];

            $tags = compact('rrd_def');
            data_update($this->getDeviceArray(), 'pf_removals', $tags, $fields);

            $this->enableGraph('pf_removals');
        }
    }
}
