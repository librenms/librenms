<?php
/**
 * Routeros.php
 *
 * Mikrotik RouterOS
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
 * @link       https://www.librenms.org
 *
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Facades\PortCache;
use App\Models\Qos;
use App\Models\Transceiver;
use Illuminate\Support\Collection;
use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Discovery\QosDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessCcqDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessDistanceDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessNoiseFloorDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessQualityDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRateDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRsrpDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRsrqDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRssiDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSinrDiscovery;
use LibreNMS\Interfaces\Discovery\TransceiverDiscovery;
use LibreNMS\Interfaces\Polling\OSPolling;
use LibreNMS\Interfaces\Polling\QosPolling;
use LibreNMS\OS;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Util\Number;

class Routeros extends OS implements
    OSPolling,
    QosDiscovery,
    QosPolling,
    TransceiverDiscovery,
    WirelessCcqDiscovery,
    WirelessClientsDiscovery,
    WirelessFrequencyDiscovery,
    WirelessNoiseFloorDiscovery,
    WirelessRateDiscovery,
    WirelessRssiDiscovery,
    WirelessDistanceDiscovery,
    WirelessRsrqDiscovery,
    WirelessRsrpDiscovery,
    WirelessSinrDiscovery,
    WirelessQualityDiscovery
{
    private Collection $qosIdxToParent;

    /**
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessCcq()
    {
        $sensors = [];

        $data = $this->getCacheTable('MIKROTIK-MIB::mtxrWlApTable');
        foreach ($data as $index => $entry) {
            // skip sensors with no data (nv2 should report 1 client, but doesn't report ccq)
            if ($entry['mtxrWlApClientCount'] > 0 && $entry['mtxrWlApOverallTxCCQ'] == 0) {
                continue;
            }
            $freq = $entry['mtxrWlApFreq'] ? substr($entry['mtxrWlApFreq'], 0, 1) . 'G' : 'SSID';

            $sensors[] = new WirelessSensor(
                'ccq',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.14988.1.1.1.3.1.10.' . $index,
                'mikrotik',
                $index,
                "$freq: " . $entry['mtxrWlApSsid'],
                $entry['mtxrWlApOverallTxCCQ']
            );
        }

        $data = $this->getCacheTable('MIKROTIK-MIB::mtxrWlStatTable');
        foreach ($data as $index => $entry) {
            $freq = $entry['mtxrWlStatFreq'] ? substr($entry['mtxrWlStatFreq'], 0, 1) . 'G' : 'SSID';
            if (empty($entry['mtxrWlStatTxCCQ']) && empty($entry['mtxrWlStatRxCCQ'])) {
                continue;
            }

            $sensors[] = new WirelessSensor(
                'ccq',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.14988.1.1.1.1.1.9.' . $index,
                'mikrotik-tx-ccq',
                $index,
                "$freq: " . $entry['mtxrWlStatSsid'] . ' Tx',
                $entry['mtxrWlStatTxCCQ']
            );
            $sensors[] = new WirelessSensor(
                'ccq',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.14988.1.1.1.1.1.10.' . $index,
                'mikrotik-rx-ccq',
                $index,
                "$freq: " . $entry['mtxrWlStatSsid'] . ' Rx',
                $entry['mtxrWlStatRxCCQ']
            );
        }

        return $sensors;
    }

    /**
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessClients()
    {
        $sensors = [];
        $data = $this->getCacheTable('MIKROTIK-MIB::mtxrWlApTable');
        foreach ($data as $index => $entry) {
            $freq = $entry['mtxrWlApFreq'] ? substr($entry['mtxrWlApFreq'], 0, 1) . 'G' : 'SSID';

            $sensors[] = new WirelessSensor(
                'clients',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.14988.1.1.1.3.1.6.' . $index,
                'mikrotik',
                $index,
                "$freq: " . $entry['mtxrWlApSsid'],
                $entry['mtxrWlApClientCount']
            );
        }

        return $sensors;
    }

    /**
     * Discover wireless frequency.  This is in MHz. Type is frequency.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessFrequency()
    {
        $sensors = [];
        $data = $this->getCacheTable('MIKROTIK-MIB::mtxrWlApTable');
        foreach ($data as $index => $entry) {
            if ($entry['mtxrWlApFreq'] === '0') {
                continue;
            }
            $freq = substr($entry['mtxrWlApFreq'], 0, 1) . 'G';
            $sensors[] = new WirelessSensor(
                'frequency',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.14988.1.1.1.3.1.7.' . $index,
                'mikrotik',
                $index,
                "$freq: " . $entry['mtxrWlApSsid'],
                $entry['mtxrWlApFreq']
            );
        }

        $data = $this->getCacheTable('MIKROTIK-MIB::mtxrWlStatTable');
        foreach ($data as $index => $entry) {
            if ($entry['mtxrWlStatFreq'] === '0') {
                continue;
            }
            $freq = substr($entry['mtxrWlStatFreq'], 0, 1) . 'G';
            $sensors[] = new WirelessSensor(
                'frequency',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.14988.1.1.1.1.1.7.' . $index,
                'mikrotik',
                $index,
                "$freq: " . $entry['mtxrWlStatSsid'],
                $entry['mtxrWlStatFreq']
            );
        }

        $data = $this->getCacheTable('MIKROTIK-MIB::mtxrWl60GTable');
        foreach ($data as $index => $entry) {
            $sensors[] = new WirelessSensor(
                'frequency',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.14988.1.1.1.8.1.6.' . $index,
                'mikrotik-60g',
                $index,
                '60G: ' . $entry['mtxrWl60GSsid'],
                $entry['mtxrWl60GFreq']
            );
        }

        return $sensors;
    }

    /**
     * Discover wireless Rssi.  This is in Dbm. Type is Dbm.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessRssi()
    {
        $sensors = [];
        $data = $this->getCacheTable('MIKROTIK-MIB::mtxrWl60GTable');

        foreach ($data as $index => $entry) {
            $sensors[] = new WirelessSensor(
                'rssi',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.14988.1.1.1.8.1.12.' . $index,
                'mikrotik',
                $index,
                '60G: ' . $entry['mtxrWl60GSsid'],
                $entry['mtxrWl60GRssi']
            );
        }

        return $sensors;
    }

    /**
     * Discover wireless Quality.  This is in Dbm. Type is Dbm.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessQuality()
    {
        $sensors = [];
        $data = $this->getCacheTable('MIKROTIK-MIB::mtxrWl60GTable');

        foreach ($data as $index => $entry) {
            $sensors[] = new WirelessSensor(
                'quality',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.14988.1.1.1.8.1.8.' . $index,
                'mikrotik',
                $index,
                '60G: ' . $entry['mtxrWl60GSsid'],
                $entry['mtxrWl60GSignal']
            );
        }

        return $sensors;
    }

    /**
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessNoiseFloor()
    {
        $sensors = [];
        $data = $this->getCacheTable('MIKROTIK-MIB::mtxrWlApTable');

        foreach ($data as $index => $entry) {
            $freq = $entry['mtxrWlApFreq'] ? substr($entry['mtxrWlApFreq'], 0, 1) . 'G' : 'SSID';

            $sensors[] = new WirelessSensor(
                'noise-floor',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.14988.1.1.1.3.1.9.' . $index,
                'mikrotik',
                $index,
                "$freq: " . $entry['mtxrWlApSsid'],
                $entry['mtxrWlApNoiseFloor']
            );
        }

        return $sensors;
    }

    /**
     * Discover wireless rate. This is in bps. Type is rate.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessRate()
    {
        $sensors = [];

        $data = $this->getCacheTable('MIKROTIK-MIB::mtxrWlApTable');
        foreach ($data as $index => $entry) {
            if ($entry['mtxrWlApTxRate'] === '0' && $entry['mtxrWlApRxRate'] === '0') {
                continue;  // no data
            }

            $freq = $entry['mtxrWlApFreq'] ? substr($entry['mtxrWlApFreq'], 0, 1) . 'G' : 'SSID';
            $sensors[] = new WirelessSensor(
                'rate',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.14988.1.1.1.3.1.2.' . $index,
                'mikrotik-tx',
                $index,
                "$freq: " . $entry['mtxrWlApSsid'] . ' Tx',
                $entry['mtxrWlApTxRate']
            );
            $sensors[] = new WirelessSensor(
                'rate',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.14988.1.1.1.3.1.3.' . $index,
                'mikrotik-rx',
                $index,
                "$freq: " . $entry['mtxrWlApSsid'] . ' Rx',
                $entry['mtxrWlApRxRate']
            );
        }

        $data = $this->getCacheTable('MIKROTIK-MIB::mtxrWlStatTable');
        foreach ($data as $index => $entry) {
            if ($entry['mtxrWlStatTxRate'] === '0' && $entry['mtxrWlStatRxRate'] === '0') {
                continue;  // no data
            }
            $freq = $entry['mtxrWlStatFreq'] ? substr($entry['mtxrWlStatFreq'], 0, 1) . 'G' : 'SSID';
            $sensors[] = new WirelessSensor(
                'rate',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.14988.1.1.1.1.1.2.' . $index,
                'mikrotik-tx',
                $index,
                "$freq: " . $entry['mtxrWlStatSsid'] . ' Tx',
                $entry['mtxrWlStatTxRate']
            );
            $sensors[] = new WirelessSensor(
                'rate',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.14988.1.1.1.1.1.3.' . $index,
                'mikrotik-rx',
                $index,
                "$freq: " . $entry['mtxrWlStatSsid'] . ' Rx',
                $entry['mtxrWlStatRxRate']
            );
        }

        $data = $this->getCacheTable('MIKROTIK-MIB::mtxrWl60GTable');
        foreach ($data as $index => $entry) {
            $sensors[] = new WirelessSensor(
                'rate',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.14988.1.1.1.8.1.13.' . $index,
                'mikrotik-60g-tx',
                $index,
                '60G: ' . $entry['mtxrWl60GSsid'],
                $entry['mtxrWl60GPhyRate'],
                1000000
            );
        }

        return $sensors;
    }

    /**
     * Discover wireless distance.  This is in Kilometers. Type is distance.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessDistance()
    {
        $sensors = [];

        $data = $this->getCacheTable('MIKROTIK-MIB::mtxrWl60GStaTable');
        foreach ($data as $index => $entry) {
            $sensors[] = new WirelessSensor(
                'distance',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.14988.1.1.1.9.1.10.' . $index,
                'mikrotik',
                $index,
                '60G: Sta > ' . $entry['mtxrWl60GStaRemote'],
                $entry['mtxrWl60GStaDistance'],
                1,
                100000
            );
        }

        return $sensors;
    }

    /**
     * Discover LTE RSRQ.  This is in Dbm. Type is Dbm.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessRsrq()
    {
        $data = $this->getCacheTable('MIKROTIK-MIB::mtxrLTEModemTable');

        $sensors = [];
        foreach ($data as $index => $entry) {
            $name = $this->getCacheByIndex('MIKROTIK-MIB::mtxrInterfaceStatsName');
            $sensors[] = new WirelessSensor(
                'rsrq',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.14988.1.1.16.1.1.3.' . $index,
                'routeros',
                $index,
                $name[$index] . ': Signal RSRQ',
                $entry['mtxrLTEModemSignalRSRQ']
            );
        }

        return $sensors;
    }

    /**
     * Discover LTE RSRP.  This is in Dbm. Type is Dbm.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessRsrp()
    {
        $data = $this->getCacheTable('MIKROTIK-MIB::mtxrLTEModemTable');

        $sensors = [];
        foreach ($data as $index => $entry) {
            $name = $this->getCacheByIndex('MIKROTIK-MIB::mtxrInterfaceStatsName');
            $sensors[] = new WirelessSensor(
                'rsrp',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.14988.1.1.16.1.1.4.' . $index,
                'routeros',
                $index,
                $name[$index] . ': Signal RSRP',
                $entry['mtxrLTEModemSignalRSRP']
            );
        }

        return $sensors;
    }

    /**
     * Discover LTE SINR.  This is in Dbm. Type is Dbm.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessSinr()
    {
        $data = $this->getCacheTable('MIKROTIK-MIB::mtxrLTEModemTable');

        $sensors = [];
        foreach ($data as $index => $entry) {
            $name = $this->getCacheByIndex('MIKROTIK-MIB::mtxrInterfaceStatsName');
            $sensors[] = new WirelessSensor(
                'sinr',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.14988.1.1.16.1.1.7.' . $index,
                'routeros',
                $index,
                $name[$index] . ': Signal SINR',
                $entry['mtxrLTEModemSignalSINR']
            );
        }

        return $sensors;
    }

    public function pollOS(DataStorageInterface $datastore): void
    {
        $leases = snmp_get($this->getDeviceArray(), 'mtxrDHCPLeaseCount.0', '-OQv', 'MIKROTIK-MIB');

        if (is_numeric($leases)) {
            $rrd_def = RrdDefinition::make()->addDataset('leases', 'GAUGE', 0);

            $fields = [
                'leases' => $leases,
            ];

            $tags = compact('rrd_def');
            $datastore->put($this->getDeviceArray(), 'routeros_leases', $tags, $fields);
            $this->enableGraph('routeros_leases');
        }

        $pppoe_sessions = snmp_get($this->getDeviceArray(), '1.3.6.1.4.1.9.9.150.1.1.1.0', '-OQv', '', '');

        if (is_numeric($pppoe_sessions)) {
            $rrd_def = RrdDefinition::make()->addDataset('pppoe_sessions', 'GAUGE', 0);

            $fields = [
                'pppoe_sessions' => $pppoe_sessions,
            ];

            $tags = compact('rrd_def');
            $datastore->put($this->getDeviceArray(), 'routeros_pppoe_sessions', $tags, $fields);
            $this->enableGraph('routeros_pppoe_sessions');
        }
    }

    public function discoverQos(): Collection
    {
        $this->qosIdxToParent = new Collection();
        $qos = new Collection();

        $qos = $qos->concat(\SnmpQuery::walk('MIKROTIK-MIB::mtxrQueueSimpleTable')->mapTable(function ($data, $qosIndex) {
            return new Qos([
                'device_id' => $this->getDeviceId(),
                'type' => 'routeros_simple',
                'title' => $data['MIKROTIK-MIB::mtxrQueueSimpleName'],
                'snmp_idx' => $qosIndex,
                'rrd_id' => $data['MIKROTIK-MIB::mtxrQueueSimpleName'],
                'ingress' => 1,
                'egress' => 1,
            ]);
        }));

        $this->qosIdxToParent->put('routeros_tree', new Collection());
        $qos = $qos->concat(\SnmpQuery::walk('MIKROTIK-MIB::mtxrQueueTreeTable')->mapTable(function ($data, $qosIndex) {
            $thisQos = new Qos([
                'device_id' => $this->getDeviceId(),
                'port_id' => PortCache::getIdFromIfIndex(hexdec($data['MIKROTIK-MIB::mtxrQueueTreeParentIndex']), $this->getDevice()),
                'type' => 'routeros_tree',
                'title' => $data['MIKROTIK-MIB::mtxrQueueTreeName'],
                'snmp_idx' => $qosIndex,
                'rrd_id' => $data['MIKROTIK-MIB::mtxrQueueTreeName'],
                'ingress' => 0,
                'egress' => 1,
            ]);

            // Save this child -> parent index into the collection for use in setQosParents();
            $this->qosIdxToParent->get('routeros_tree')->put($qosIndex, hexdec($data['MIKROTIK-MIB::mtxrQueueTreeParentIndex']));

            return $thisQos;
        }));

        return $qos;
    }

    public function setQosParents($qos)
    {
        $qos->each(function (Qos $thisQos, int $key) use ($qos) {
            $qosParentMap = $this->qosIdxToParent->get($thisQos->type);
            if (! $qosParentMap) {
                // Parent data does not exist
                return;
            }

            $parent_idx = $qosParentMap->get($thisQos->snmp_idx);

            if ($parent_idx) {
                $parent = $qos->where('type', $thisQos->type)->where('snmp_idx', $parent_idx)->first();

                if ($parent) {
                    $parent_id = $parent->qos_id;
                } else {
                    $parent_id = null;
                }
            } else {
                $parent_id = null;
            }

            $thisQos->parent_id = $parent_id;
            $thisQos->save();
        });
    }

    public function pollQos($qos)
    {
        $poll_time = time();
        $treeNames = \SnmpQuery::walk('MIKROTIK-MIB::mtxrQueueTreeName')->table(1);
        $treeBytes = \SnmpQuery::walk('MIKROTIK-MIB::mtxrQueueTreeHCBytes')->table(1);
        // Packet counters are not updating
        //$treePackets = \SnmpQuery::walk('MIKROTIK-MIB::mtxrQueueTreePackets')->table(1);
        $treeDrops = \SnmpQuery::walk('MIKROTIK-MIB::mtxrQueueTreeDropped')->table(1);

        $simpleNames = \SnmpQuery::walk('MIKROTIK-MIB::mtxrQueueSimpleName')->table(1);
        $simpleBytesIn = \SnmpQuery::walk('MIKROTIK-MIB::mtxrQueueSimpleBytesIn')->table(1);
        $simpleBytesOut = \SnmpQuery::walk('MIKROTIK-MIB::mtxrQueueSimpleBytesOut')->table(1);
        // Packet counters are not updating
        //$simplePacketsIn = \SnmpQuery::walk('MIKROTIK-MIB::mtxrQueueSimplePacketsIn')->table(1);
        //$simplePacketsOut = \SnmpQuery::walk('MIKROTIK-MIB::mtxrQueueSimplePacketsOut')->table(1);
        $simpleDropsIn = \SnmpQuery::walk('MIKROTIK-MIB::mtxrQueueSimpleDroppedIn')->table(1);
        $simpleDropsOut = \SnmpQuery::walk('MIKROTIK-MIB::mtxrQueueSimpleDroppedOut')->table(1);

        $qos->each(function (Qos $thisQos, int $key) use ($poll_time, $treeNames, $treeBytes, $treeDrops, $simpleNames, $simpleBytesIn, $simpleBytesOut, $simpleDropsIn, $simpleDropsOut) {
            switch ($thisQos->type) {
                case 'routeros_tree':
                    if (! array_key_exists($thisQos->snmp_idx, $treeNames)) {
                        d_echo('Ignoring queue tree ' . $thisQos->rrd_id . " because was not found SNMP\n");
                        break;
                    }
                    if ($treeNames[$thisQos->snmp_idx]['MIKROTIK-MIB::mtxrQueueTreeName'] != $thisQos->rrd_id) {
                        d_echo('Ignoring queue tree ' . $thisQos->rrd_id . " because it does not match SNMP\n");
                        break;
                    }
                    d_echo('Updating queue tree ' . $thisQos->rrd_id . "\n");
                    $thisQos->last_polled = $poll_time;
                    $thisQos->last_bytes_in = null;
                    $thisQos->last_bytes_out = $treeBytes[$thisQos->snmp_idx]['MIKROTIK-MIB::mtxrQueueTreeHCBytes'];
                    $thisQos->last_bytes_drop_in = null;
                    $thisQos->last_bytes_drop_out = null;
                    $thisQos->last_packets_in = null;
                    // Packet counters are not updating
                    //$thisQos->last_packets_out = $treePackets[$thisQos->snmp_idx]['MIKROTIK-MIB::mtxrQueueTreePackets'];
                    $thisQos->last_packets_out = null;
                    $thisQos->last_packets_drop_in = null;
                    $thisQos->last_packets_drop_out = $treeDrops[$thisQos->snmp_idx]['MIKROTIK-MIB::mtxrQueueTreeDropped'];
                    break;
                case 'routeros_simple':
                    if (! array_key_exists($thisQos->snmp_idx, $simpleNames)) {
                        d_echo('Ignoring simple tree ' . $thisQos->rrd_id . " because was not found SNMP\n");
                        break;
                    }
                    if ($simpleNames[$thisQos->snmp_idx]['MIKROTIK-MIB::mtxrQueueSimpleName'] != $thisQos->rrd_id) {
                        d_echo('Ignoring simple tree ' . $thisQos->rrd_id . " because it does not match SNMP\n");
                        break;
                    }
                    d_echo('Updating simple tree ' . $thisQos->rrd_id . "\n");
                    $thisQos->last_polled = $poll_time;
                    $thisQos->last_bytes_in = $simpleBytesIn[$thisQos->snmp_idx]['MIKROTIK-MIB::mtxrQueueSimpleBytesIn'];
                    $thisQos->last_bytes_out = $simpleBytesOut[$thisQos->snmp_idx]['MIKROTIK-MIB::mtxrQueueSimpleBytesOut'];
                    $thisQos->last_bytes_drop_in = null;
                    $thisQos->last_bytes_drop_out = null;
                    // Packet counters are not updating
                    //$thisQos->last_packets_in = $simplePacketsIn[$thisQos->snmp_idx]['MIKROTIK-MIB::mtxrQueueSimplePacketsIn'];
                    //$thisQos->last_packets_out = $simplePacketsOut[$thisQos->snmp_idx]['MIKROTIK-MIB::mtxrQueueSimplePacketsOut'];
                    $thisQos->last_packets_in = null;
                    $thisQos->last_packets_out = null;
                    $thisQos->last_packets_drop_in = $simpleDropsIn[$thisQos->snmp_idx]['MIKROTIK-MIB::mtxrQueueSimpleDroppedIn'];
                    $thisQos->last_packets_drop_out = $simpleDropsOut[$thisQos->snmp_idx]['MIKROTIK-MIB::mtxrQueueSimpleDroppedOut'];
                    break;
                default:
                    echo 'Queue type ' . $thisQos->type . " has not been implmeneted in LibreNMS/OS/Routeros.php\n";
            }
        });
    }

    public function discoverTransceivers(): Collection
    {
        return \SnmpQuery::walk('MIKROTIK-MIB::mtxrOpticalTable')->mapTable(function ($data, $ifIndex) {
            $wavelength = isset($data['MIKROTIK-MIB::mtxrOpticalWavelength']) && $data['MIKROTIK-MIB::mtxrOpticalWavelength'] != '.00' ? Number::cast($data['MIKROTIK-MIB::mtxrOpticalWavelength']) : null;

            return new Transceiver([
                'port_id' => (int) PortCache::getIdFromIfIndex($ifIndex, $this->getDevice()),
                'index' => $ifIndex,
                'vendor' => $data['MIKROTIK-MIB::mtxrOpticalVendorName'] ?? null,
                'serial' => $data['MIKROTIK-MIB::mtxrOpticalVendorSerial'] ?? null,
                'wavelength' => $wavelength == 65535 ? null : $wavelength, // NA value = 65535.00
                'entity_physical_index' => $ifIndex,
            ]);
        });
    }
}
