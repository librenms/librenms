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
    private $data;

    /**
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessCcq()
    {
        $data = $this->fetchData();
        $sensors = [];
        foreach ($data as $index => $entry) {
            // skip sensors with no data (nv2 should report 1 client, but doesn't report ccq)
            if ($entry['mtxrWlApClientCount'] > 0 && $entry['mtxrWlApOverallTxCCQ'] == 0) {
                continue;
            }

            $sensors[] = new WirelessSensor(
                'ccq',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.14988.1.1.1.3.1.10.' . $index,
                'mikrotik',
                $index,
                'SSID: ' . $entry['mtxrWlApSsid'],
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
        return $this->discoverSensor(
            'clients',
            'mtxrWlApClientCount',
            '.1.3.6.1.4.1.14988.1.1.1.3.1.6.'
        );
    }

    /**
     * Discover wireless frequency.  This is in MHz. Type is frequency.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessFrequency()
    {
        $data = $this->fetchData();

        foreach ($data as $index => $entry) {
            if ($entry['mtxrWlApFreq'] == null) {
                return $this->discoverSensor(
                    'frequency',
                    'mtxrWl60GFreq',
                    '.1.3.6.1.4.1.14988.1.1.1.8.1.6.'
                );
            } else {
                return $this->discoverSensor(
                    'frequency',
                    'mtxrWlApFreq',
                    '.1.3.6.1.4.1.14988.1.1.1.3.1.7.'
                );
            }
        }

        return [];
    }

    /**
     * Discover wireless Rssi.  This is in Dbm. Type is Dbm.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessRssi()
    {
        return $this->discoverSensor(
            'rssi',
            'mtxrWl60GRssi',
            '.1.3.6.1.4.1.14988.1.1.1.8.1.12.'
        );
    }

    /**
     * Discover wireless Quality.  This is in Dbm. Type is Dbm.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessQuality()
    {
        return $this->discoverSensor(
            'quality',
            'mtxrWl60GSignal',
            '.1.3.6.1.4.1.14988.1.1.1.8.1.8.'
        );
    }

    /**
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessNoiseFloor()
    {
        return $this->discoverSensor(
            'noise-floor',
            'mtxrWlApNoiseFloor',
            '.1.3.6.1.4.1.14988.1.1.1.3.1.9.'
        );
    }

    /**
     * Discover wireless rate. This is in bps. Type is rate.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessRate()
    {
        $data = $this->fetchData();
        $sensors = [];
        foreach ($data as $index => $entry) {
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
            $sensors[] = new WirelessSensor(
                'rate',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.14988.1.1.1.9.1.8.' . $index,
                'mikrotik-60g-tx',
                $index,
                'Tx Rate',
                $entry['mtxrWl60G'],
                $multiplier = 1000000
            );
        }

        return $sensors;
    }

    private function fetchData()
    {
        if (is_null($this->data)) {
            $wl60 = snmpwalk_cache_oid($this->getDeviceArray(), 'mtxrWl60GTable', [], 'MIKROTIK-MIB');
            $wlap = snmpwalk_cache_oid($this->getDeviceArray(), 'mtxrWlApTable', [], 'MIKROTIK-MIB');
            $wl60sta = snmpwalk_cache_oid($this->getDeviceArray(), 'mtxrWl60GStaTable', [], 'MIKROTIK-MIB');
            $this->data = $wl60 + $wlap;
            $this->data = $this->data + $wl60sta;
        }

        return $this->data;
    }

    public function discoverWirelessDistance()
    {
        $data = $this->fetchData();
        $sensors = [];
        foreach ($data as $index => $entry) {
            $sensors[] = new WirelessSensor(
                'distance',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.14988.1.1.1.8.1.13.' . $index,
                'mikrotik',
                $index,
                'SSID: ' . $entry['mtxrWl60GSsid'],
                $entry['mtxrWl60G'],
                null,
                100000
            );
        }

        return $sensors;
    }

    private function discoverSensor($type, $oid, $num_oid_base): array
    {
        $data = $this->fetchData();
        $sensors = [];
        foreach ($data as $index => $entry) {
            if (($entry['mtxrWlApSsid'] !== null)) {
                $sensors[] = new WirelessSensor(
                    $type,
                    $this->getDeviceId(),
                    $num_oid_base . $index,
                    'mikrotik',
                    $index,
                    'SSID: ' . $entry['mtxrWlApSsid'],
                    $entry[$oid]
                );
            } else {
                $sensors[] = new WirelessSensor(
                    $type,
                    $this->getDeviceId(),
                    $num_oid_base . $index,
                    'mikrotik',
                    $index,
                    'SSID: ' . $entry['mtxrWl60GSsid'],
                    $entry[$oid]
                );
            }
        }

        return $sensors;
    }

    public function discoverWirelessRsrq()
    {
        $sinr = '.1.3.6.1.4.1.14988.1.1.16.1.1.3.1'; //MIKROTIK-MIB::mtxrLTEModemSignalRSRQ

        return [
            new WirelessSensor(
                'rsrq',
                $this->getDeviceId(),
                $sinr,
                'routeros',
                0,
                'Signal RSRQ',
                null
            ),
        ];
    }

    public function discoverWirelessRsrp()
    {
        $sinr = '.1.3.6.1.4.1.14988.1.1.16.1.1.4.1'; //MIKROTIK-MIB::mtxrLTEModemSignalRSRP

        return [
            new WirelessSensor(
                'rsrp',
                $this->getDeviceId(),
                $sinr,
                'routeros',
                0,
                'Signal RSRP',
                null
            ),
        ];
    }

    public function discoverWirelessSinr()
    {
        $sinr = '.1.3.6.1.4.1.14988.1.1.16.1.1.7.1'; //MIKROTIK-MIB::mtxrLTEModemSignalSINR

        return [
            new WirelessSensor(
                'sinr',
                $this->getDeviceId(),
                $sinr,
                'routeros',
                0,
                'Signal SINR',
                null
            ),
        ];
    }

    public function pollOS()
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
