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
use LibreNMS\Enum\WirelessSensorType;
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
use SnmpQuery;

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
                WirelessSensorType::Frequency,
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
                WirelessSensorType::Capacity,
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
                WirelessSensorType::NoiseFloor,
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
                WirelessSensorType::Power,
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
        $data = SnmpQuery::walk('LCOS-LX-MIB::lcosLXStatusWLANStationEntryPhySignal')->valuesByIndex();
        $data = SnmpQuery::cache()->walk([
            'LCOS-LX-MIB::lcosLXStatusWLANStationEntryNetworkName',
            'LCOS-LX-MIB::lcosLXStatusWLANStationEntryIPv4Address',
        ])->valuesByIndex($data);

        $sensors = [];
        foreach ($data as $index => $entry) {
            $bssid = $entry['LCOS-LX-MIB::lcosLXStatusWLANStationEntryIPv4Address'] ?? null;
            if ($bssid === null || isset($sensors[$bssid])) {
                continue;
            }

            if (! isset($entry['LCOS-LX-MIB::lcosLXStatusWLANStationEntryPhySignal'])) {
                continue;
            }

            $sensors[$bssid] = new WirelessSensor(
                WirelessSensorType::Ccq,
                $this->getDeviceId(),
                '.1.3.6.1.4.1.2356.13.1.3.44.1.10.' . Mac::parse($bssid)->oid() . '.0',
                'lcoslx',
                $index,
                'CCQ ' . $entry['LCOS-LX-MIB::lcosLXStatusWLANStationEntryNetworkName'] . " $bssid",
                $entry['LCOS-LX-MIB::lcosLXStatusWLANStationEntryPhySignal']
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
        $data = SnmpQuery::walk([
            'LCOS-LX-MIB::lcosLXStatusWLANStationEntryEffTxRate',
            'LCOS-LX-MIB::lcosLXStatusWLANStationEntryEffRxRate',
        ])->valuesByIndex();
        $data = SnmpQuery::cache()->walk([
            'LCOS-LX-MIB::lcosLXStatusWLANStationEntryNetworkName',
            'LCOS-LX-MIB::lcosLXStatusWLANStationEntryIPv4Address',
        ])->valuesByIndex($data);

        $sensors = [];
        foreach ($data as $entry) {
            $bssid = $entry['LCOS-LX-MIB::lcosLXStatusWLANStationEntryIPv4Address'] ?? null;
            if ($bssid === null || isset($sensors[$bssid])) {
                continue;
            }

            if (isset($entry['LCOS-LX-MIB::lcosLXStatusWLANStationEntryEffTxRate'])) {
                $sensors['tx-' . $bssid] = new WirelessSensor(
                    WirelessSensorType::Rate,
                    $this->getDeviceId(),
                    '.1.3.6.1.4.1.2356.13.1.3.32.1.50.' . Mac::parse($bssid)->oid() . '.0',
                    'lcos-tx',
                    $bssid,
                    'TX Rate ' . $entry['LCOS-LX-MIB::lcosLXStatusWLANStationEntryNetworkName'] . " $bssid",
                    $entry['LCOS-LX-MIB::lcosLXStatusWLANStationEntryEffTxRate'],
                    1000000
                );
            }
            if (isset($entry['LCOS-LX-MIB::lcosLXStatusWLANStationEntryEffRxRate'])) {
                $sensors['rx-' . $bssid] = new WirelessSensor(
                    WirelessSensorType::Rate,
                    $this->getDeviceId(),
                    '.1.3.6.1.4.1.2356.13.1.3.32.1.51.' . Mac::parse($bssid)->oid() . '.0',
                    'lcos-rx',
                    $bssid,
                    'RX Rate ' . $entry['LCOS-LX-MIB::lcosLXStatusWLANStationEntryNetworkName'] . " $bssid",
                    $entry['LCOS-LX-MIB::lcosLXStatusWLANStationEntryEffRxRate'],
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
        $data = SnmpQuery::walk([
            'LCOS-LX-MIB::lcosLXStatusWLANCompetingNetworksEntrySignalLevel',
            'LCOS-LX-MIB::lcosLXStatusWLANCompetingNetworksEntryInterpointPeerName',
            'LCOS-LX-MIB::lcosLXStatusWLANCompetingNetworksEntryBssid',
        ])->valuesByIndex();

        $sensors = [];

        foreach ($data as $entry) {
            $bssid = $entry['LCOS-LX-MIB::lcosLXStatusWLANCompetingNetworksEntryBssid'] ?? null;
            if ($bssid === null || isset($sensors[$bssid]) || ! isset($entry['LCOS-LX-MIB::lcosLXStatusWLANCompetingNetworksEntrySignalLevel'])) {
                continue;
            }

            $sensors[$bssid] = new WirelessSensor(
                WirelessSensorType::Rssi,
                $this->getDeviceId(),
                '.1.3.6.1.4.1.2356.13.1.3.44.1.26.' . Mac::parse($bssid)->oid() . '.0',
                'lcoslx',
                $bssid,
                'RSSI ' . ($entry['LCOS-LX-MIB::lcosLXStatusWLANCompetingNetworksEntryInterpointPeerName'] ?? '') . " $bssid",
                $entry['LCOS-LX-MIB::lcosLXStatusWLANCompetingNetworksEntrySignalLevel']
            );
        }

        return $sensors;
    }
}
