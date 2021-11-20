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

use LibreNMS\Device\WirelessSensor;
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
use LibreNMS\Interfaces\Polling\OSPolling;
use LibreNMS\OS;
use LibreNMS\RRD\RrdDefinition;

class Routeros extends OS implements
    OSPolling,
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

            $sensors[] = new WirelessSensor(
                'rate',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.14988.1.1.1.3.1.2.' . $index,
                'mikrotik-tx',
                $index,
                'SSID: ' . $entry['mtxrWlApSsid'] . ' Tx',
                $entry['mtxrWlApTxRate']
            );
            $sensors[] = new WirelessSensor(
                'rate',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.14988.1.1.1.3.1.3.' . $index,
                'mikrotik-rx',
                $index,
                'SSID: ' . $entry['mtxrWlApSsid'] . ' Rx',
                $entry['mtxrWlApRxRate']
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
                1000
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

    public function pollOS(): void
    {
        $leases = snmp_get($this->getDeviceArray(), 'mtxrDHCPLeaseCount.0', '-OQv', 'MIKROTIK-MIB');

        if (is_numeric($leases)) {
            $rrd_def = RrdDefinition::make()->addDataset('leases', 'GAUGE', 0);

            $fields = [
                'leases' => $leases,
            ];

            $tags = compact('rrd_def');
            data_update($this->getDeviceArray(), 'routeros_leases', $tags, $fields);
            $this->enableGraph('routeros_leases');
        }

        $pppoe_sessions = snmp_get($this->getDeviceArray(), '1.3.6.1.4.1.9.9.150.1.1.1.0', '-OQv', '', '');

        if (is_numeric($pppoe_sessions)) {
            $rrd_def = RrdDefinition::make()->addDataset('pppoe_sessions', 'GAUGE', 0);

            $fields = [
                'pppoe_sessions' => $pppoe_sessions,
            ];

            $tags = compact('rrd_def');
            data_update($this->getDeviceArray(), 'routeros_pppoe_sessions', $tags, $fields);
            $this->enableGraph('routeros_pppoe_sessions');
        }
    }
}
