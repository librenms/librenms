<?php
/*
 * Pfsense.php
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

class Pfsense extends Unix implements OSPolling
{
    public function pollOS()
    {
        $oids = snmp_get_multi($this->getDeviceArray(), [
            'pfStateTableCount.0',
            'pfStateTableSearches.0',
            'pfStateTableInserts.0',
            'pfStateTableRemovals.0',
            'pfCounterMatch.0',
            'pfCounterBadOffset.0',
            'pfCounterFragment.0',
            'pfCounterShort.0',
            'pfCounterNormalize.0',
            'pfCounterMemDrop.0',
        ], '-OQUs', 'BEGEMOT-PF-MIB');

        if (is_numeric($oids[0]['pfStateTableCount'])) {
            $rrd_def = RrdDefinition::make()->addDataset('states', 'GAUGE', 0);

            $fields = [
                'states' => $oids[0]['pfStateTableCount'],
            ];

            $tags = compact('rrd_def');
            data_update($this->getDeviceArray(), 'pf_states', $tags, $fields);

            $this->enableGraph('pf_states');
        }

        if (is_numeric($oids[0]['pfStateTableSearches'])) {
            $rrd_def = RrdDefinition::make()->addDataset('searches', 'COUNTER', 0);

            $fields = [
                'searches' => $oids[0]['pfStateTableSearches'],
            ];

            $tags = compact('rrd_def');
            data_update($this->getDeviceArray(), 'pf_searches', $tags, $fields);

            $this->enableGraph('pf_searches');
        }

        if (is_numeric($oids[0]['pfStateTableInserts'])) {
            $rrd_def = RrdDefinition::make()->addDataset('inserts', 'COUNTER', 0);

            $fields = [
                'inserts' => $oids[0]['pfStateTableInserts'],
            ];

            $tags = compact('rrd_def');
            data_update($this->getDeviceArray(), 'pf_inserts', $tags, $fields);

            $this->enableGraph('pf_inserts');
        }

        if (is_numeric($oids[0]['pfStateTableCount'])) {
            $rrd_def = RrdDefinition::make()->addDataset('removals', 'COUNTER', 0);

            $fields = [
                'removals' => $oids[0]['pfStateTableCount'],
            ];

            $tags = compact('rrd_def');
            data_update($this->getDeviceArray(), 'pf_removals', $tags, $fields);

            $this->enableGraph('pf_removals');
        }

        if (is_numeric($oids[0]['pfCounterMatch'])) {
            $rrd_def = RrdDefinition::make()->addDataset('matches', 'COUNTER', 0);

            $fields = [
                'matches' => $oids[0]['pfCounterMatch'],
            ];

            $tags = compact('rrd_def');
            data_update($this->getDeviceArray(), 'pf_matches', $tags, $fields);

            $this->enableGraph('pf_matches');
        }

        if (is_numeric($oids[0]['pfCounterBadOffset'])) {
            $rrd_def = RrdDefinition::make()->addDataset('badoffset', 'COUNTER', 0);

            $fields = [
                'badoffset' => $oids[0]['pfCounterBadOffset'],
            ];

            $tags = compact('rrd_def');
            data_update($this->getDeviceArray(), 'pf_badoffset', $tags, $fields);

            $this->enableGraph('pf_badoffset');
        }

        if (is_numeric($oids[0]['pfCounterFragment'])) {
            $rrd_def = RrdDefinition::make()->addDataset('fragmented', 'COUNTER', 0);

            $fields = [
                'fragmented' => $oids[0]['pfCounterFragment'],
            ];

            $tags = compact('rrd_def');
            data_update($this->getDeviceArray(), 'pf_fragmented', $tags, $fields);

            $this->enableGraph('pf_fragmented');
        }

        if (is_numeric($oids[0]['pfCounterShort'])) {
            $rrd_def = RrdDefinition::make()->addDataset('short', 'COUNTER', 0);

            $fields = [
                'short' => $oids[0]['pfCounterShort'],
            ];

            $tags = compact('rrd_def');
            data_update($this->getDeviceArray(), 'pf_short', $tags, $fields);

            $this->enableGraph('pf_short');
        }

        if (is_numeric($oids[0]['pfCounterNormalize'])) {
            $rrd_def = RrdDefinition::make()->addDataset('normalized', 'COUNTER', 0);

            $fields = [
                'normalized' => $oids[0]['pfCounterNormalize'],
            ];

            $tags = compact('rrd_def');
            data_update($this->getDeviceArray(), 'pf_normalized', $tags, $fields);

            $this->enableGraph('pf_normalized');
        }

        if (is_numeric($oids[0]['pfCounterMemDrop'])) {
            $rrd_def = RrdDefinition::make()->addDataset('memdropped', 'COUNTER', 0);

            $fields = [
                'memdropped' => $oids[0]['pfCounterMemDrop'],
            ];

            $tags = compact('rrd_def');
            data_update($this->getDeviceArray(), 'pf_memdropped', $tags, $fields);

            $this->enableGraph('pf_memdropped');
        }
    }
}
