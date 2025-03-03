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

use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Polling\OSPolling;
use LibreNMS\OS\Shared\Unix;
use LibreNMS\RRD\RrdDefinition;

class Openbsd extends Unix implements OSPolling
{
    public function pollOS(DataStorageInterface $datastore): void
    {
        $oids = \SnmpQuery::get([
            'OPENBSD-PF-MIB::pfStateCount.0',
            'OPENBSD-PF-MIB::pfStateSearches.0',
            'OPENBSD-PF-MIB::pfStateInserts.0',
            'OPENBSD-PF-MIB::pfStateRemovals.0',
            'OPENBSD-PF-MIB::pfCntMatch.0',
            'OPENBSD-PF-MIB::pfCntBadOffset.0',
            'OPENBSD-PF-MIB::pfCntFragment.0',
            'OPENBSD-PF-MIB::pfCntShort.0',
            'OPENBSD-PF-MIB::pfCntNormalize.0',
            'OPENBSD-PF-MIB::pfCntMemory.0',
            'OPENBSD-PF-MIB::pfCntTimestamp.0',
            'OPENBSD-PF-MIB::pfCntCongestion.0',
            'OPENBSD-PF-MIB::pfCntIpOption.0',
            'OPENBSD-PF-MIB::pfCntProtoCksum.0',
            'OPENBSD-PF-MIB::pfCntStateMismatch.0',
            'OPENBSD-PF-MIB::pfCntStateInsert.0',
            'OPENBSD-PF-MIB::pfCntStateLimit.0',
            'OPENBSD-PF-MIB::pfCntSrcLimit.0',
            'OPENBSD-PF-MIB::pfCntSynproxy.0',
            'OPENBSD-PF-MIB::pfCntTranslate.0',
            'OPENBSD-PF-MIB::pfCntNoRoute.0',
        ])->values();

        $this->graphOID('states', $datastore, ['states' => $oids['OPENBSD-PF-MIB::pfStateCount.0']], 'GAUGE');
        $this->graphOID('searches', $datastore, ['searches' => $oids['OPENBSD-PF-MIB::pfStateSearches.0']]);
        $this->graphOID('inserts', $datastore, ['inserts' => $oids['OPENBSD-PF-MIB::pfStateInserts.0']]);
        $this->graphOID('removals', $datastore, ['removals' => $oids['OPENBSD-PF-MIB::pfStateRemovals.0']]);
        $this->graphOID('matches', $datastore, ['matches' => $oids['OPENBSD-PF-MIB::pfCntMatch.0']]);

        $this->graphOID('drops', $datastore, [
            'badoffset' => $oids['OPENBSD-PF-MIB::pfCntBadOffset.0'],
            'fragmented' => $oids['OPENBSD-PF-MIB::pfCntFragment.0'],
            'short' => $oids['OPENBSD-PF-MIB::pfCntShort.0'],
            'normalized' => $oids['OPENBSD-PF-MIB::pfCntNormalize.0'],
            'memory' => $oids['OPENBSD-PF-MIB::pfCntMemory.0'],
            'timestamp' => $oids['OPENBSD-PF-MIB::pfCntTimestamp.0'],
            'congestion' => $oids['OPENBSD-PF-MIB::pfCntCongestion.0'],
            'ipoption' => $oids['OPENBSD-PF-MIB::pfCntIpOption.0'],
            'protocksum' => $oids['OPENBSD-PF-MIB::pfCntProtoCksum.0'],
            'statemismatch' => $oids['OPENBSD-PF-MIB::pfCntStateMismatch.0'],
            'stateinsert' => $oids['OPENBSD-PF-MIB::pfCntStateInsert.0'],
            'statelimit' => $oids['OPENBSD-PF-MIB::pfCntStateLimit.0'],
            'srclimit' => $oids['OPENBSD-PF-MIB::pfCntSrcLimit.0'],
            'synproxy' => $oids['OPENBSD-PF-MIB::pfCntSynproxy.0'],
            'translate' => $oids['OPENBSD-PF-MIB::pfCntTranslate.0'],
            'noroute' => $oids['OPENBSD-PF-MIB::pfCntNoRoute.0'],
        ]);
    }

    private function graphOID(string $graphName, DataStorageInterface $datastore, array $oids, string $type = 'COUNTER'): void
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
        $datastore->put($this->getDeviceArray(), "pf_$graphName", $tags, $fields);

        $this->enableGraph("pf_$graphName");
    }
}
