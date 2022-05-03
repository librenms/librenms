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

        $this->parseOID($oids[0]['pfStateCount'], 'states', 'GAUGE');
        $this->parseOID($oids[0]['pfStateSearches'], 'searches');
        $this->parseOID($oids[0]['pfStateInserts'], 'inserts');
        $this->parseOID($oids[0]['pfStateRemovals'], 'removals');

        $this->parseOID($oids[0]['pfCntMatch'], 'matches');
        $this->parseOID($oids[0]['pfCntBadOffset'], 'badoffset');
        $this->parseOID($oids[0]['pfCntFragment'], 'fragmented');
        $this->parseOID($oids[0]['pfCntShort'], 'short');
        $this->parseOID($oids[0]['pfCntNormalize'], 'normalized');
        $this->parseOID($oids[0]['pfCntMemory'], 'memdropped');

        $this->parseOID($oids[0]['pfCntTimestamp'], 'timestamp');
        $this->parseOID($oids[0]['pfCntCongestion'], 'congestion');
        $this->parseOID($oids[0]['pfCntIpOption'], 'ipoption');
        $this->parseOID($oids[0]['pfCntProtoCksum'], 'badchecksum');
        $this->parseOID($oids[0]['pfCntStateMismatch'], 'badstate');
        $this->parseOID($oids[0]['pfCntStateInsert'], 'badinsert');
        $this->parseOID($oids[0]['pfCntStateLimit'], 'statelimit');
        $this->parseOID($oids[0]['pfCntSrcLimit'], 'srclimit');
        $this->parseOID($oids[0]['pfCntSynproxy'], 'synproxy');
        $this->parseOID($oids[0]['pfCntTranslate'], 'translate');
        $this->parseOID($oids[0]['pfCntNoRoute'], 'noroute');
    }

    private function parseOID(?string $oid, string $field, string $type = 'COUNTER'): void
    {
        if (is_numeric($oid ?? null)) {
            $rrd_def = RrdDefinition::make()->addDataset($field, $type, 0);

            $fields = [
                $field => $oid,
            ];

            $tags = compact('rrd_def');
            data_update($this->getDeviceArray(), "pf_$field", $tags, $fields);

            $this->enableGraph("pf_$field");
        }
    }
}
