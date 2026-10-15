<?php

/**
 * Ray3.php
 *
 * Racom.eu
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
 * @copyright  2020 Martin Kukal
 * @author     Martin Kukal <martin@kukal.cz>
 */

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Enum\WirelessSensorType;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessMseDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessPowerDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRateDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRssiDiscovery;
use LibreNMS\OS;

class Ray3 extends OS implements
    WirelessFrequencyDiscovery,
    WirelessPowerDiscovery,
    WirelessRssiDiscovery,
    WirelessRateDiscovery,
    WirelessMseDiscovery
{
    /**
     * Discover wireless frequency. This is in MHz. Type is frequency.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessFrequency()
    {
        return [
            // RAY3-MIB::txFreq.0 (kHz)
            new WirelessSensor(WirelessSensorType::Frequency, $this->getDeviceId(), '.1.3.6.1.4.1.33555.4.2.1.4.0', 'racom3-tx', 1, 'TX Frequency', null, 1, 1000),
            // RAY3-MIB::rxFreq.0 (kHz)
            new WirelessSensor(WirelessSensorType::Frequency, $this->getDeviceId(), '.1.3.6.1.4.1.33555.4.2.1.3.0', 'racom3-rx', 1, 'RX Frequency', null, 1, 1000),
        ];
    }

    /**
     * Discover wireless tx or rx power. This is in dBm. Type is power.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessPower()
    {
        return [
            // RAY3-MIB::rfPowerCurrent.0
            new WirelessSensor(WirelessSensorType::Power, $this->getDeviceId(), '.1.3.6.1.4.1.33555.4.2.1.17.0', 'racom3-pow-cur', 1, 'Tx Power Current'),
            // RAY3-MIB::rfPowerConfigured.0
            new WirelessSensor(WirelessSensorType::Power, $this->getDeviceId(), '.1.3.6.1.4.1.33555.4.2.1.12.0', 'racom3-pow-conf', 1, 'Tx Power Configured'),
        ];
    }

    /**
     * Discover wireless RSSI (Received Signal Strength Indicator). This is in dBm. Type is rssi.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessRssi()
    {
        $oid = '.1.3.6.1.4.1.33555.4.3.2.1.0'; // RAY3-MIB::rss.0

        return [
            new WirelessSensor(WirelessSensorType::Rssi, $this->getDeviceId(), $oid, 'racom3', 1, 'RSSI', null, 1, 10),
        ];
    }

    /**
     * Discover wireless MSE. This is in dB. Type is mse.
     * RAy3 has no SNR/CINR object; RAY3-MIB::mse replaces it.
     *
     * @return array Sensors
     */
    public function discoverWirelessMse()
    {
        $oid = '.1.3.6.1.4.1.33555.4.3.2.12.0'; // RAY3-MIB::mse.0

        return [
            new WirelessSensor(WirelessSensorType::Mse, $this->getDeviceId(), $oid, 'racom3', 1, 'MSE', null, 1, 10),
        ];
    }

    /**
     * Discover wireless RATE.  This is in bps. Type is rate.
     *
     * Ethernet throughput gauges complement IF-MIB counters (and work when
     * only SNMPv1 is practical on slower radios).
     *
     * @return array Sensors
     */
    public function discoverWirelessRate()
    {
        return [
            // RAY3-MIB::netBitrate.0 (kbps)
            new WirelessSensor(WirelessSensorType::Rate, $this->getDeviceId(), '.1.3.6.1.4.1.33555.4.2.1.13.0', 'racom3-netBitrate', 1, 'Net Bitrate', null, 1000, 1),
            // RAY3-MIB::maxNetBitrate.0 (Mbps)
            new WirelessSensor(WirelessSensorType::Rate, $this->getDeviceId(), '.1.3.6.1.4.1.33555.4.2.1.14.0', 'racom3-maxNetBitrate', 2, 'Max Net Bitrate', null, 1000000, 1),
            // RAY3-MIB::ethInThroughput.0 (kbps)
            new WirelessSensor(WirelessSensorType::Rate, $this->getDeviceId(), '.1.3.6.1.4.1.33555.4.3.3.1.0', 'racom3-eth1-in', 3, 'Eth1 In', null, 1000, 1),
            // RAY3-MIB::ethOutThroughput.0 (kbps)
            new WirelessSensor(WirelessSensorType::Rate, $this->getDeviceId(), '.1.3.6.1.4.1.33555.4.3.3.2.0', 'racom3-eth1-out', 4, 'Eth1 Out', null, 1000, 1),
            // RAY3-MIB::eth2InThroughput.0 (kbps)
            new WirelessSensor(WirelessSensorType::Rate, $this->getDeviceId(), '.1.3.6.1.4.1.33555.4.3.3.3.0', 'racom3-eth2-in', 5, 'Eth2 In', null, 1000, 1),
            // RAY3-MIB::eth2OutThroughput.0 (kbps)
            new WirelessSensor(WirelessSensorType::Rate, $this->getDeviceId(), '.1.3.6.1.4.1.33555.4.3.3.4.0', 'racom3-eth2-out', 6, 'Eth2 Out', null, 1000, 1),
        ];
    }
}
