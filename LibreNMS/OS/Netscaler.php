<?php
/*
 * Netscaler.php
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
use LibreNMS\RRD\RrdDefinition;

class Netscaler extends \LibreNMS\OS implements OSPolling
{
    public function pollOS()
    {
        echo ' IP';

        // These are at the start of large trees that we don't want to walk the entirety of, so we snmp_get_multi them
        $oids_gauge = [
            'tcpCurServerConn',
            'tcpCurClientConn',
            'tcpActiveServerConn',
            'tcpCurClientConnClosing',
            'tcpCurServerConnEstablished',
            'tcpCurClientConnOpening',
            'tcpCurClientConnEstablished',
            'tcpCurServerConnClosing',
            'tcpSpareConn',
            'tcpSurgeQueueLen',
            'tcpCurServerConnOpening',
            'tcpCurPhysicalServers',
            'tcpReuseHit',
        ];

        $oids_counter = [
            'tcpTotServerConnOpened',
            'tcpTotServerConnClosed',
            'tcpTotClientConnOpened',
            'tcpTotClientConnClosed',
            'tcpTotSyn',
            'tcpTotSynProbe',
            'tcpTotSvrFin',
            'tcpTotCltFin',
            'tcpTotRxPkts',
            'tcpTotRxBytes',
            'tcpTotTxPkts',
            'tcpTotTxBytes',
            'tcpWaitToSyn',
            'tcpTotZombieCltConnFlushed',
            'tcpTotZombieSvrConnFlushed',
            'tcpTotZombieHalfOpenCltConnFlushed',
            'tcpTotZombieHalfOpenSvrConnFlushed',
            'tcpTotZombieActiveHalfCloseCltConnFlushed',
            'tcpTotZombieActiveHalfCloseSvrConnFlushed',
            'tcpTotZombiePassiveHalfCloseCltConnFlushed',
            'tcpTotZombiePassiveHalfCloseSrvConnFlushed',
            'tcpErrBadCheckSum',
            'tcpErrSynInSynRcvd',
            'tcpErrSynInEst',
            'tcpErrSynGiveUp',
            'tcpErrSynSentBadAck',
            'tcpErrSynRetry',
            'tcpErrFinRetry',
            'tcpErrFinGiveUp',
            'tcpErrFinDup',
            'tcpErrRst',
            'tcpErrRstNonEst',
            'tcpErrRstOutOfWindow',
            'tcpErrRstInTimewait',
            'tcpErrSvrRetrasmit',
            'tcpErrCltRetrasmit',
            'tcpErrFullRetrasmit',
            'tcpErrPartialRetrasmit',
            'tcpErrSvrOutOfOrder',
            'tcpErrCltOutOfOrder',
            'tcpErrCltHole',
            'tcpErrSvrHole',
            'tcpErrCookiePktSeqReject',
            'tcpErrCookiePktSigReject',
            'tcpErrCookiePktSeqDrop',
            'tcpErrCookiePktMssReject',
            'tcpErrRetransmit',
            'tcpErrRetransmitGiveUp',
            'pcbTotZombieCall',
            'tcpTotSynHeld',
            'tcpTotSynFlush',
            'tcpTotFinWaitClosed',
            'tcpErrAnyPortFail',
            'tcpErrIpPortFail',
            'tcpErrSentRst',
            'tcpErrBadStateConn',
            'tcpErrFastRetransmissions',
            'tcpErrFirstRetransmissions',
            'tcpErrSecondRetransmissions',
            'tcpErrThirdRetransmissions',
            'tcpErrForthRetransmissions',
            'tcpErrFifthRetransmissions',
            'tcpErrSixthRetransmissions',
            'tcpErrSeventhRetransmissions',
            'tcpErrDataAfterFin',
            'tcpErrRstThreshold',
            'tcpErrOutOfWindowPkts',
            'tcpErrSynDroppedCongestion',
            'tcpWaitData',
            'tcpErrStrayPkt',
        ];

        $oids = array_merge($oids_gauge, $oids_counter);

        $data = snmpwalk_cache_oid($this->getDeviceArray(), 'nsTcpStatsGroup', [], 'NS-ROOT-MIB');

        $shorten = [
            'tcp',
            'Active',
            'Passive',
            'Zombie',
        ];
        $short_replacement = [
            '',
            'Ac',
            'Ps',
            'Zom',
        ];

        $rrd_def = new RrdDefinition();
        foreach ($oids_gauge as $oid) {
            $oid_ds = str_replace($shorten, $short_replacement, $oid);
            $rrd_def->addDataset($oid_ds, 'GAUGE', null, 100000000000);
        }
        foreach ($oids_counter as $oid) {
            $oid_ds = str_replace($shorten, $short_replacement, $oid);
            $rrd_def->addDataset($oid_ds, 'COUNTER', null, 100000000000);
        }

        $fields = [];
        foreach ($oids as $oid) {
            $fields[$oid] = is_numeric($data[0][$oid]) ? ':' . $data[0][$oid] : 'U';
        }

        $tags = compact('rrd_def');
        data_update($this->getDeviceArray(), 'netscaler-stats-tcp', $tags, $fields);

        $this->enableGraph('netscaler_tcp_conn');
        $this->enableGraph('netscaler_tcp_bits');
        $this->enableGraph('netscaler_tcp_pkts');
    }
}
