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
    public function pollOS(): void
    {
        $oids = snmp_get_multi($this->getDeviceArray(), [
            'pfStateCount.0',
            'pfStateSearches.0',
            'pfStateInserts.0',
            'pfStateRemovals.0',
            'pfCntMatch.0',
            'pfCntBadOffset.0',
            'pfCntFragment.0',
            'pfCntShort.0',
            'pfCntNormalize.0',
            'pfCntMemory.0',
            'pfCntTimestamp.0',
            'pfCntCongestion.0',
            'pfCntIpOption.0',
            'pfCntProtoCksum.0',
            'pfCntStateMismatch.0',
            'pfCntStateInsert.0',
            'pfCntStateLimit.0',
            'pfCntSrcLimit.0',
            'pfCntSynproxy.0',
            'pfCntTranslate.0',
            'pfCntNoRoute.0',
        ], '-OQUs', 'OPENBSD-PF-MIB');

        $this->parseOID('states', ['states' => $oids[0]['pfStateCount']], 'GAUGE');
        $this->parseOID('searches', ['searches' => $oids[0]['pfStateSearches']]);
        $this->parseOID('inserts', ['inserts' => $oids[0]['pfStateInserts']]);
        $this->parseOID('removals', ['removals' => $oids[0]['pfStateRemovals']]);
        $this->parseOID('matches', ['matches' => $oids[0]['pfCntMatch']]);

        $this->parseOID('drops', [
            'badoffset' => $oids[0]['pfCntBadOffset'],
            'fragmented' => $oids[0]['pfCntFragment'],
            'short' => $oids[0]['pfCntShort'],
            'normalized' => $oids[0]['pfCntNormalize'],
            'memory' => $oids[0]['pfCntMemory'],
            'timestamp' => $oids[0]['pfCntTimestamp'],
            'congestion' => $oids[0]['pfCntCongestion'],
            'ipoption' => $oids[0]['pfCntIpOption'],
            'protocksum' => $oids[0]['pfCntProtoCksum'],
            'statemismatch' => $oids[0]['pfCntStateMismatch'],
            'stateinsert' => $oids[0]['pfCntStateInsert'],
            'statelimit' => $oids[0]['pfCntStateLimit'],
            'srclimit' => $oids[0]['pfCntSrcLimit'],
            'synproxy' => $oids[0]['pfCntSynproxy'],
            'translate' => $oids[0]['pfCntTranslate'],
            'noroute' => $oids[0]['pfCntNoRoute'],
        ]);
    }

    private function parseOID(string $graphName, array $oids, string $type = 'COUNTER'): void
    {
        $rrd_def = RrdDefinition::make();
        $fields = [];
        foreach ($oids as $field => $oid) {
            if (is_numeric($oid ?? null)) {
                $rrd_def->addDataset($field, $type, 0);
                $fields[$field] = $oid;
            }
        }
        $tags = compact('rrd_def');
        data_update($this->getDeviceArray(), "pf_$graphName", $tags, $fields);

        $this->enableGraph("pf_$graphName");
    }
}
