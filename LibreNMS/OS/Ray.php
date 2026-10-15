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
use SnmpQuery;

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
        $data = SnmpQuery::numeric()->get([
            '.1.3.6.1.4.1.33555.1.2.1.4.0', // RAY-MIB::txFreq.0 (kHz)
            '.1.3.6.1.4.1.33555.1.2.1.3.0', // RAY-MIB::rxFreq.0 (kHz)
        ])->values();

        return [
            new WirelessSensor(WirelessSensorType::Frequency, $this->getDeviceId(), '.1.3.6.1.4.1.33555.1.2.1.4.0', 'racom-tx', 1, 'TX Frequency', $this->sensorValue($data, '.1.3.6.1.4.1.33555.1.2.1.4.0'), 1, 1000),
            new WirelessSensor(WirelessSensorType::Frequency, $this->getDeviceId(), '.1.3.6.1.4.1.33555.1.2.1.3.0', 'racom-rx', 1, 'RX Frequency', $this->sensorValue($data, '.1.3.6.1.4.1.33555.1.2.1.3.0'), 1, 1000),
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
        $data = SnmpQuery::numeric()->get([
            '.1.3.6.1.4.1.33555.1.2.1.17.0', // RAY-MIB::rfPowerCurrent.0
            '.1.3.6.1.4.1.33555.1.2.1.12.0', // RAY-MIB::rfPowerConfigured.0
        ])->values();

        return [
            new WirelessSensor(WirelessSensorType::Power, $this->getDeviceId(), '.1.3.6.1.4.1.33555.1.2.1.17.0', 'racom-pow-cur', 1, 'Tx Power Current', $this->sensorValue($data, '.1.3.6.1.4.1.33555.1.2.1.17.0')),
            new WirelessSensor(WirelessSensorType::Power, $this->getDeviceId(), '.1.3.6.1.4.1.33555.1.2.1.12.0', 'racom-pow-conf', 1, 'Tx Power Configured', $this->sensorValue($data, '.1.3.6.1.4.1.33555.1.2.1.12.0')),
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
        $data = SnmpQuery::numeric()->get($oid)->values();

        return [
            new WirelessSensor(WirelessSensorType::Rssi, $this->getDeviceId(), $oid, 'racom', 1, 'RSSI', $this->sensorValue($data, $oid), 1, 10),
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
        $data = SnmpQuery::numeric()->get($oid)->values();

        return [
            new WirelessSensor(WirelessSensorType::Snr, $this->getDeviceId(), $oid, 'racom', 1, 'CINR', $this->sensorValue($data, $oid), 1, 10),
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
        $oids = [
            '.1.3.6.1.4.1.33555.1.2.1.13.0', // RAY-MIB::netBitrate.0 (kbps)
            '.1.3.6.1.4.1.33555.1.2.1.14.0', // RAY-MIB::maxNetBitrate.0 (Mbps)
            '.1.3.6.1.4.1.33555.1.3.3.1.0', // RAY-MIB::ethInThroughput.0 (kbps)
            '.1.3.6.1.4.1.33555.1.3.3.2.0', // RAY-MIB::ethOutThroughput.0 (kbps)
            '.1.3.6.1.4.1.33555.1.3.3.3.0', // RAY-MIB::eth2InThroughput.0 (kbps)
            '.1.3.6.1.4.1.33555.1.3.3.4.0', // RAY-MIB::eth2OutThroughput.0 (kbps)
        ];
        $data = SnmpQuery::numeric()->get($oids)->values();

        return [
            new WirelessSensor(WirelessSensorType::Rate, $this->getDeviceId(), $oids[0], 'racom-netBitrate', 1, 'Net Bitrate', $this->sensorValue($data, $oids[0]), 1000, 1),
            new WirelessSensor(WirelessSensorType::Rate, $this->getDeviceId(), $oids[1], 'racom-maxNetBitrate', 2, 'Max Net Bitrate', $this->sensorValue($data, $oids[1]), 1000000, 1),
            new WirelessSensor(WirelessSensorType::Rate, $this->getDeviceId(), $oids[2], 'racom-eth1-in', 3, 'Eth1 In', $this->sensorValue($data, $oids[2]), 1000, 1),
            new WirelessSensor(WirelessSensorType::Rate, $this->getDeviceId(), $oids[3], 'racom-eth1-out', 4, 'Eth1 Out', $this->sensorValue($data, $oids[3]), 1000, 1),
            new WirelessSensor(WirelessSensorType::Rate, $this->getDeviceId(), $oids[4], 'racom-eth2-in', 5, 'Eth2 In', $this->sensorValue($data, $oids[4]), 1000, 1),
            new WirelessSensor(WirelessSensorType::Rate, $this->getDeviceId(), $oids[5], 'racom-eth2-out', 6, 'Eth2 Out', $this->sensorValue($data, $oids[5]), 1000, 1),
        ];
    }

    private function sensorValue(array $data, string $oid): ?float
    {
        if (! isset($data[$oid]) || ! is_numeric($data[$oid])) {
            return null;
        }

        return (float) $data[$oid];
    }
}
