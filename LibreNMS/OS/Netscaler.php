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

use App\Models\Sensor;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use LibreNMS\Enum\SensorType;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Polling\OSPolling;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Util\Oid;
use SnmpQuery;

class Netscaler extends \LibreNMS\OS implements OSPolling
{
    public function pollOS(DataStorageInterface $datastore): void
    {
        Log::info('IP:');

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
            $fields[$oid] = $data[0][$oid] ?? null;
        }

        $tags = ['rrd_def' => $rrd_def];
        $datastore->put($this->getDeviceArray(), 'netscaler-stats-tcp', $tags, $fields);

        $this->enableGraph('netscaler_tcp_conn');
        $this->enableGraph('netscaler_tcp_bits');
        $this->enableGraph('netscaler_tcp_pkts');
    }

    /**
     * Health counters are named by their index, the name tells the type
     */
    protected function discoverOsSensors(): Collection
    {
        $sensors = new Collection;

        foreach (SnmpQuery::walk('NS-ROOT-MIB::sysHealthCounterValue')->table(1) as $name => $data) {
            $name = trim((string) $name, '"');
            $value = $data['NS-ROOT-MIB::sysHealthCounterValue'] ?? null;
            $divisor = 1;
            if (str_contains($name, 'Temp')) {
                $type = SensorType::Temperature;
            } elseif (str_contains($name, 'Fan')) {
                $type = SensorType::Fanspeed;
            } elseif (str_contains($name, 'Volt') || str_contains($name, 'Vtt')) {
                $type = SensorType::Voltage;
                $divisor = 1000;
            } else {
                continue;
            }

            if (is_numeric($value)) {
                $sensors->push(new Sensor([
                    'poller_type' => 'snmp',
                    'sensor_class' => $type->value,
                    'sensor_oid' => '.1.3.6.1.4.1.5951.4.1.1.41.7.1.2.' . Oid::encodeString($name),
                    'sensor_index' => $name,
                    'sensor_type' => 'netscaler-health',
                    'sensor_descr' => $name,
                    'sensor_divisor' => $divisor,
                    'sensor_multiplier' => 1,
                    'sensor_current' => $value / $divisor,
                    'rrd_type' => 'GAUGE',
                ]));
            }
        }

        return $sensors;
    }
}
