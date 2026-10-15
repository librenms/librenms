<?php

/**
 * Ray.php
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
 * @link       https://www.librenms.org
 */

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Enum\WirelessSensorType;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessPowerDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRateDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRssiDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSnrDiscovery;
use LibreNMS\OS;

class Ray extends OS implements
    WirelessFrequencyDiscovery,
    WirelessPowerDiscovery,
    WirelessRssiDiscovery,
    WirelessRateDiscovery,
    WirelessSnrDiscovery
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
            // RAY-MIB::txFreq.0 (kHz)
            new WirelessSensor(WirelessSensorType::Frequency, $this->getDeviceId(), '.1.3.6.1.4.1.33555.1.2.1.4.0', 'racom-tx', 1, 'TX Frequency', null, 1, 1000),
            // RAY-MIB::rxFreq.0 (kHz)
            new WirelessSensor(WirelessSensorType::Frequency, $this->getDeviceId(), '.1.3.6.1.4.1.33555.1.2.1.3.0', 'racom-rx', 1, 'RX Frequency', null, 1, 1000),
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
            // RAY-MIB::rfPowerCurrent.0
            new WirelessSensor(WirelessSensorType::Power, $this->getDeviceId(), '.1.3.6.1.4.1.33555.1.2.1.17.0', 'racom-pow-cur', 1, 'Tx Power Current'),
            // RAY-MIB::rfPowerConfigured.0
            new WirelessSensor(WirelessSensorType::Power, $this->getDeviceId(), '.1.3.6.1.4.1.33555.1.2.1.12.0', 'racom-pow-conf', 1, 'Tx Power Configured'),
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
        $oid = '.1.3.6.1.4.1.33555.1.3.2.1.0'; // RAY-MIB::rss.0

        return [
            new WirelessSensor(WirelessSensorType::Rssi, $this->getDeviceId(), $oid, 'racom', 1, 'RSSI', null, 1, 10),
        ];
    }

    /**
     * Discover wireless SNR.  This is in dB. Type is snr.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessSnr()
    {
        $oid = '.1.3.6.1.4.1.33555.1.3.2.2.0'; // RAY-MIB::snr.0

        return [
            new WirelessSensor(WirelessSensorType::Snr, $this->getDeviceId(), $oid, 'racom', 1, 'CINR', null, 1, 10),
        ];
    }

    /**
     * Discover wireless RATE.  This is in bps. Type is rate.
     *
     * RAy2 exposes no IF-MIB 32-bit octet counters and SNMPv1 cannot fetch
     * ifHC* Counter64 values, so ethernet throughput gauges are used for traffic.
     *
     * @return array Sensors
     */
    public function discoverWirelessRate()
    {
        return [
            // RAY-MIB::netBitrate.0 (kbps)
            new WirelessSensor(WirelessSensorType::Rate, $this->getDeviceId(), '.1.3.6.1.4.1.33555.1.2.1.13.0', 'racom-netBitrate', 1, 'Net Bitrate', null, 1000, 1),
            // RAY-MIB::maxNetBitrate.0 (Mbps)
            new WirelessSensor(WirelessSensorType::Rate, $this->getDeviceId(), '.1.3.6.1.4.1.33555.1.2.1.14.0', 'racom-maxNetBitrate', 2, 'Max Net Bitrate', null, 1000000, 1),
            // RAY-MIB::ethInThroughput.0 (kbps) — usable over SNMPv1
            new WirelessSensor(WirelessSensorType::Rate, $this->getDeviceId(), '.1.3.6.1.4.1.33555.1.3.3.1.0', 'racom-eth1-in', 3, 'Eth1 In', null, 1000, 1),
            // RAY-MIB::ethOutThroughput.0 (kbps)
            new WirelessSensor(WirelessSensorType::Rate, $this->getDeviceId(), '.1.3.6.1.4.1.33555.1.3.3.2.0', 'racom-eth1-out', 4, 'Eth1 Out', null, 1000, 1),
            // RAY-MIB::eth2InThroughput.0 (kbps)
            new WirelessSensor(WirelessSensorType::Rate, $this->getDeviceId(), '.1.3.6.1.4.1.33555.1.3.3.3.0', 'racom-eth2-in', 5, 'Eth2 In', null, 1000, 1),
            // RAY-MIB::eth2OutThroughput.0 (kbps)
            new WirelessSensor(WirelessSensorType::Rate, $this->getDeviceId(), '.1.3.6.1.4.1.33555.1.3.3.4.0', 'racom-eth2-out', 6, 'Eth2 Out', null, 1000, 1),
        ];
    }
}
