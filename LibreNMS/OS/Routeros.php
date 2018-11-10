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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessCcqDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessNoiseFloorDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRateDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRssiDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessDistanceDiscovery;
use LibreNMS\OS;

class Routeros extends OS implements
    WirelessCcqDiscovery,
    WirelessClientsDiscovery,
    WirelessFrequencyDiscovery,
    WirelessNoiseFloorDiscovery,
    WirelessRateDiscovery,
    WirelessRssiDiscovery,
    WirelessDistanceDiscovery
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
        $sensors = array();
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
       
        $sensors = array();
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
        $sensors = array();
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
            $wl60 = snmpwalk_cache_oid($this->getDevice(), 'mtxrWl60GTable', array(), 'MIKROTIK-MIB');
            $wlap = snmpwalk_cache_oid($this->getDevice(), 'mtxrWlApTable', array(), 'MIKROTIK-MIB');
            $wl60sta = snmpwalk_cache_oid($this->getDevice(), 'mtxrWl60GStaTable', array(), 'MIKROTIK-MIB');
            $this->data = $wl60+$wlap;
            $this->data = $this->data+$wl60sta;
        }
        return $this->data;
    }
    public function discoverWirelessDistance()
    {
        $data = $this->fetchData();
        $sensors = array();
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
    private function discoverSensor($type, $oid, $num_oid_base)
    {
        $data = $this->fetchData();
        $sensors = array();
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
}
