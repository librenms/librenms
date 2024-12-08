<?php
/**
 * Lcoslx.php
 *
 * Lancom LCOS LX
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
 * @copyright  2024 Rudy Broersma
 * @author     Rudy Broersma <tozz@kijkt.tv>
 */

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessCapacityDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessCcqDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessNoiseFloorDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessPowerDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRateDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRssiDiscovery;
use LibreNMS\Interfaces\Polling\Sensors\WirelessFrequencyPolling;
use LibreNMS\OS;
use LibreNMS\Util\Mac;

class Lcoslx extends OS implements
    WirelessFrequencyDiscovery,
    WirelessFrequencyPolling,
    WirelessCapacityDiscovery,
    WirelessNoiseFloorDiscovery,
    WirelessPowerDiscovery,
    WirelessCcqDiscovery,
    WirelessRateDiscovery,
    WirelessRssiDiscovery
{
    /**
     * Convert String to decimal encoded string notation
     *
     * @param  string  $index
     * @return string decimal encoded OID string
     */
    private function strToDecOid($index)
    {
        $dec_index = [];
        for ($i = 0, $j = strlen($index); $i < $j; $i++) {
            $dec_index[] = ord($index[$i]);
        }

        return implode('.', $dec_index);
    }

    /**
     * Discover wireless frequency.  This is in Hz. Type is frequency.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessFrequency()
    {
        $data = snmpwalk_cache_oid($this->getDeviceArray(), 'lcosLXStatusWLANRadiosEntryRadioChannel', [], 'LCOS-LX-MIB');
        $sensors = [];
        foreach ($data as $index => $entry) {
            $sensors[$index] = new WirelessSensor(
                'frequency',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.2356.13.1.3.57.1.3.' . '6.' . $this->strToDecOid($index),
                'lcoslx',
                $index,
                "Frequency ($index)",
                WirelessSensor::channelToFrequency($entry['lcosLXStatusWLANRadiosEntryRadioChannel'])
            );
        }

        return $sensors;
    }

    /**
     * Poll wireless frequency as MHz
     * The returned array should be sensor_id => value pairs
     *
     * @param  array  $sensors  Array of sensors needed to be polled
     * @return array of polled data
     */
    public function pollWirelessFrequency(array $sensors)
    {
        return  $this->pollWirelessChannelAsFrequency($sensors);
    }

    /**
     * Discover wireless capacity.  This is a percent. Type is capacity.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessCapacity()
    {
        $data = snmpwalk_cache_oid($this->getDeviceArray(), 'lcosLXStatusWLANRadiosEntryModemLoad', [], 'LCOS-LX-MIB');
        $sensors = [];
        foreach ($data as $index => $entry) {
            $sensors[$index] = new WirelessSensor(
                'capacity',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.2356.13.1.3.57.1.6.' . '6.' . $this->strToDecOid($index),
                'lcoslx',
                $index,
                "Modem Load ($index)",
                $entry['lcosLXStatusWLANRadiosEntryModemLoad']
            );
        }

        return $sensors;
    }

    /**
     * Discover wireless noise floor. This is in dBm/Hz. Type is noise-floor.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessNoiseFloor()
    {
        $data = snmpwalk_cache_oid($this->getDeviceArray(), 'lcosLXStatusWLANRadiosEntryNoiseLevel', [], 'LCOS-LX-MIB');

        $sensors = [];
        foreach ($data as $index => $entry) {
            $sensors[$index] = new WirelessSensor(
                'noise-floor',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.2356.13.1.3.57.1.5.' . '6.' . $this->strToDecOid($index),
                'lcoslx',
                $index,
                "Noise Floor ($index)",
                $entry['lcosLXStatusWLANRadiosEntryNoiseLevel']
            );
        }

        return $sensors;
    }

    /**
     * Discover wireless tx or rx power. This is in dBm. Type is power.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessPower()
    {
        $data = snmpwalk_cache_oid($this->getDeviceArray(), 'lcosLXStatusWLANRadiosEntryTransmitPower', [], 'LCOS-LX-MIB');
        $sensors = [];

        foreach ($data as $index => $entry) {
            $sensors[$index] = new WirelessSensor(
                'power',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.2356.13.1.3.57.1.7.' . '6.' . $this->strToDecOid($index),
                'lcos-tx',
                $index,
                "Tx Power ($index)",
                $entry['lcosLXStatusWLANRadiosEntryTransmitPower']
            );
        }

        return $sensors;
    }

    /**
     * Discover wireless client connection quality.  This is a percent. Type is ccq.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessCcq()
    {
        $data = snmpwalk_cache_oid($this->getDeviceArray(), 'lcosLXStatusWLANStationEntryPhySignal', [], 'LCOS-LX-MIB');
        $data = snmpwalk_cache_oid($this->getDeviceArray(), 'lcosLXStatusWLANStationEntryNetworkName', $data, 'LCOS-LX-MIB');
        $ipv4addresses = $this->getCacheByIndex('lcosLXStatusWLANStationEntryIPv4Address', 'LCOS-LX-MIB');

        $sensors = [];
        foreach ($data as $index => $entry) {
            $bssid = $ipv4addresses[$index];
            if (isset($sensors[$bssid])) {
                continue;
            }

            $sensors[$bssid] = new WirelessSensor(
                'ccq',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.2356.13.1.3.44.1.10.' . Mac::parse($bssid)->oid() . '.0',
                'lcoslx',
                $index,
                'CCQ ' . $entry['lcosLXStatusWLANStationEntryNetworkName'] . " $bssid",
                $entry['lcosLXStatusWLANStationEntryPhySignal']
            );
        }

        return $sensors;
    }

    /**
     * Discover wireless Tx rate. This is in bps. Type is rate.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessRate()
    {
        $data = snmpwalk_cache_oid($this->getDeviceArray(), 'lcosLXStatusWLANStationEntryEffTxRate', [], 'LCOS-LX-MIB');
        $data = snmpwalk_cache_oid($this->getDeviceArray(), 'lcosLXStatusWLANStationEntryEffRxRate', $data, 'LCOS-LX-MIB');
        $data = snmpwalk_cache_oid($this->getDeviceArray(), 'lcosLXStatusWLANStationEntryNetworkName', $data, 'LCOS-LX-MIB');
        $ipv4addresses = $this->getCacheByIndex('lcosLXStatusWLANStationEntryIPv4Address', 'LCOS-LX-MIB');

        $sensors = [];
        foreach ($data as $index => $entry) {
            $bssid = $ipv4addresses[$index];
            if (isset($sensors[$bssid])) {
                continue;
            }

            if (isset($entry['lcosLXStatusWLANStationEntryEffTxRate'])) {
                $sensors['tx-' . $bssid] = new WirelessSensor(
                    'rate',
                    $this->getDeviceId(),
                    '.1.3.6.1.4.1.2356.13.1.3.32.1.50.' . Mac::parse($bssid)->oid() . '.0',
                    'lcos-tx',
                    $bssid,
                    'TX Rate ' . $entry['lcosLXStatusWLANStationEntryNetworkName'] . " $bssid",
                    $entry['lcosLXStatusWLANStationEntryEffTxRate'],
                    1000000
                );
            }
            if (isset($entry['lcosLXStatusWLANStationEntryEffRxRate'])) {
                $sensors['rx-' . $bssid] = new WirelessSensor(
                    'rate',
                    $this->getDeviceId(),
                    '.1.3.6.1.4.1.2356.13.1.3.32.1.51.' . Mac::parse($bssid)->oid() . '.0',
                    'lcos-rx',
                    $bssid,
                    'RX Rate ' . $entry['lcosLXStatusWLANStationEntryNetworkName'] . " $bssid",
                    $entry['lcosLXStatusWLANStationEntryEffTxRate'],
                    1000000
                );
            }
        }

        return $sensors;
    }

    /**
     * Discover wireless RSSI (Received Signal Strength Indicator). This is in dBm. Type is rssi.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessRssi()
    {
        $data = snmpwalk_cache_oid($this->getDeviceArray(), 'lcosLXStatusWLANCompetingNetworksEntrySignalLevel', [], 'LCOS-LX-MIB');
        $data = snmpwalk_cache_oid($this->getDeviceArray(), 'lcosLXStatusWLANCompetingNetworksEntryInterpointPeerName', $data, 'LCOS-LX-MIB');
        $bssids = $this->getCacheByIndex('lcosLXStatusWLANCompetingNetworksEntryBssid', 'LCOS-LX-MIB');

        $sensors = [];

        foreach ($data as $index => $entry) {
            $bssid = $bssids[$index];
            if (isset($sensors[$bssid])) {
                continue;
            }

            $sensors[$bssid] = new WirelessSensor(
                'rssi',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.2356.13.1.3.44.1.26.' . Mac::parse($bssid)->oid() . '.0',
                'lcoslx',
                $bssid,
                'RSSI ' . $entry['lcosLXStatusWLANCompetingNetworksEntryInterpointPeerName'] . " $bssid",
                $entry['lcosLXStatusWLANCompetingNetworksEntrySignalLevel']
            );
        }

        return $sensors;
    }
}
