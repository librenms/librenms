<?php
/**
 * Lcos.php
 *
 * Lancom LCOS
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
 * @copyright  2019 Vitali Kari
 * @author     Vitali Kari <vitali.kari@gmail.com>
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
use LibreNMS\Util\Rewrite;

class Lcos extends OS implements
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
     * @param string $index
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
        $data = snmpwalk_cache_oid($this->getDeviceArray(), 'lcsStatusWlanRadiosEntryRadioChannel', [], 'LCOS-MIB');
        $radios = $this->getCacheByIndex('lcsStatusWlanRadiosEntryIfc', 'LCOS-MIB');

        $sensors = [];
        foreach ($data as $index => $entry) {
            $radio = $radios[$index];
            if (isset($sensors[$radio])) {
                continue;
            }
            $sensors[$radio] = new WirelessSensor(
                'frequency',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.2356.11.1.3.57.1.3.' . '6.' . $this->strToDecOid($index),
                'lcos',
                $radio,
                "Frequency ($radio)",
                WirelessSensor::channelToFrequency($entry['lcsStatusWlanRadiosEntryRadioChannel'])
            );
        }

        return $sensors;
    }

    /**
     * Poll wireless frequency as MHz
     * The returned array should be sensor_id => value pairs
     *
     * @param array $sensors Array of sensors needed to be polled
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
        $data = snmpwalk_cache_oid($this->getDeviceArray(), 'lcsStatusWlanRadiosEntryModemLoad', [], 'LCOS-MIB');
        $radios = $this->getCacheByIndex('lcsStatusWlanRadiosEntryIfc', 'LCOS-MIB');

        $sensors = [];
        foreach ($data as $index => $entry) {
            $radio = $radios[$index];
            if (isset($sensors[$radio])) {
                continue;
            }
            $sensors[$radio] = new WirelessSensor(
                'capacity',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.2356.11.1.3.57.1.6.' . '6.' . $this->strToDecOid($index),
                'lcos',
                $radio,
                "Modem Load ($radio)",
                $entry['lcsStatusWlanRadiosEntryModemLoad']
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
        $data = snmpwalk_cache_oid($this->getDeviceArray(), 'lcsStatusWlanRadiosEntryNoiseLevel', [], 'LCOS-MIB');
        $radios = $this->getCacheByIndex('lcsStatusWlanRadiosEntryIfc', 'LCOS-MIB');

        $sensors = [];
        foreach ($data as $index => $entry) {
            $radio = $radios[$index];
            if (isset($sensors[$radio])) {
                continue;
            }
            $sensors[$radio] = new WirelessSensor(
                'noise-floor',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.2356.11.1.3.57.1.5.' . '6.' . $this->strToDecOid($index),
                'lcos',
                $radio,
                "Noise Floor ($radio)",
                $entry['lcsStatusWlanRadiosEntryNoiseLevel']
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
        $data = snmpwalk_cache_oid($this->getDeviceArray(), 'lcsStatusWlanRadiosEntryTransmitPower', [], 'LCOS-MIB');
        $radios = $this->getCacheByIndex('lcsStatusWlanRadiosEntryIfc', 'LCOS-MIB');

        $sensors = [];

        foreach ($data as $index => $entry) {
            $radio = $radios[$index];
            if (isset($sensors[$radio])) {
                continue;
            }
            $sensors[$radio] = new WirelessSensor(
                'power',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.2356.11.1.3.57.1.7.' . '6.' . $this->strToDecOid($index),
                'lcos-tx',
                $radio,
                "Tx Power ($radio)",
                $entry['lcsStatusWlanRadiosEntryTransmitPower']
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
        $data = snmpwalk_cache_oid($this->getDeviceArray(), 'lcsStatusWlanCompetingNetworksEntryPhySignal', [], 'LCOS-MIB');
        $data = snmpwalk_cache_oid($this->getDeviceArray(), 'lcsStatusWlanCompetingNetworksEntryInterpointPeerName', $data, 'LCOS-MIB');
        $bssids = $this->getCacheByIndex('lcsStatusWlanCompetingNetworksEntryBssid', 'LCOS-MIB');

        $sensors = [];
        foreach ($data as $index => $entry) {
            $bssid = $bssids[$index];
            if (isset($sensors[$bssid])) {
                continue;
            }

            $sensors[$bssid] = new WirelessSensor(
                'ccq',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.2356.11.1.3.44.1.10.' . Rewrite::oidMac($bssid) . '.0',
                'lcos',
                $bssid,
                'CCQ ' . $entry['lcsStatusWlanCompetingNetworksEntryInterpointPeerName'] . " $bssid",
                $entry['lcsStatusWlanCompetingNetworksEntryPhySigal']
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
        $data = snmpwalk_cache_oid($this->getDeviceArray(), 'lcsStatusWlanCompetingNetworksEntryEffRate', [], 'LCOS-MIB');
        $data = snmpwalk_cache_oid($this->getDeviceArray(), 'lcsStatusWlanCompetingNetworksEntryInterpointPeerName', $data, 'LCOS-MIB');
        $bssids = $this->getCacheByIndex('lcsStatusWlanCompetingNetworksEntryBssid', 'LCOS-MIB');

        $sensors = [];
        foreach ($data as $index => $entry) {
            $bssid = $bssids[$index];
            if (isset($sensors[$bssid])) {
                continue;
            }

            $sensors[$bssid] = new WirelessSensor(
                'rate',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.2356.11.1.3.44.1.35.' . Rewrite::oidMac($bssid) . '.0',
                'lcos-tx',
                $bssid,
                'TX Rate ' . $entry['lcsStatusWlanCompetingNetworksEntryInterpointPeerName'] . " $bssid",
                $entry['lcsStatusWlanCompetingNetworksEntryEffRate'],
                1000000
            );
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
        $data = snmpwalk_cache_oid($this->getDeviceArray(), 'lcsStatusWlanCompetingNetworksEntrySignalLevel', [], 'LCOS-MIB');
        $data = snmpwalk_cache_oid($this->getDeviceArray(), 'lcsStatusWlanCompetingNetworksEntryInterpointPeerName', $data, 'LCOS-MIB');
        $bssids = $this->getCacheByIndex('lcsStatusWlanCompetingNetworksEntryBssid', 'LCOS-MIB');

        $sensors = [];

        foreach ($data as $index => $entry) {
            $bssid = $bssids[$index];
            if (isset($sensors[$bssid])) {
                continue;
            }

            $sensors[$bssid] = new WirelessSensor(
                'rssi',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.2356.11.1.3.44.1.26.' . Rewrite::oidMac($bssid) . '.0',
                'lcos',
                $bssid,
                'RSSI ' . $entry['lcsStatusWlanCompetingNetworksEntryInterpointPeerName'] . " $bssid",
                $entry['lcsStatusWlanCompetingNetworksEntrySignalLevel']
            );
        }

        return $sensors;
    }
}
