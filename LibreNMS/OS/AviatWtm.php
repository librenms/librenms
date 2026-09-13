<?php

/**
 * AviatWtm.php
 *
 * Aviat WTM
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
 * @copyright  2020 Josh Baird
 * @author     Josh Baird<joshbaird@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\Device;
use LibreNMS\Device\WirelessSensor;
use LibreNMS\Enum\WirelessSensorType;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessPowerDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRateDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRssiDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSnrDiscovery;
use LibreNMS\OS;
use SnmpQuery;

class AviatWtm extends OS implements
    OSDiscovery,
    WirelessFrequencyDiscovery,
    WirelessRateDiscovery,
    WirelessRssiDiscovery,
    WirelessSnrDiscovery,
    WirelessPowerDiscovery
{
    /**
     * Discover OS version, hardware and serial.
     *
     * The yaml baseline reads these from fixed entPhysicalIndex 2, which holds the
     * chassis on WTM 4000. That works for hardware and serial, but the software
     * revision is only populated on a different entity (index 4 on AOS 6.2), so
     * version came back empty. Entity indexes are also renumbered by the radio
     * across reboots and module changes, so rather than hardcode another index we
     * take the first non-empty entPhysicalSoftwareRev in the table.
     */
    public function discoverOS(Device $device): void
    {
        parent::discoverOS($device); // yaml baseline: hardware, serial

        if (empty($device->version)) {
            $versions = SnmpQuery::hideMib()->walk('ENTITY-MIB::entPhysicalSoftwareRev')->table(1);

            foreach ($versions as $entity) {
                $version = trim((string) ($entity['entPhysicalSoftwareRev'] ?? ''));
                if ($version !== '') {
                    $device->version = $version;
                    break;
                }
            }
        }
    }

    /**
     * Discover wireless tx or rx power. This is in dBm. Type is power.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessFrequency()
    {
        $sensors = [];
        $name = $this->getCacheByIndex('entPhysicalName', 'ENTITY-MIB');
        $frequency = snmpwalk_cache_oid($this->getDeviceArray(), 'aviatRfFreqTx', [], 'AVIAT-RF-MIB:');
        foreach ($frequency as $index => $data) {
            $sensors[] = new WirelessSensor(
                WirelessSensorType::Frequency,
                $this->getDeviceId(),
                ".1.3.6.1.4.1.2509.9.5.2.1.1.1.$index",
                'aviat-wtm-carrier-tx-freq',
                $index,
                "TX Frequency ({$name[$index]})",
                $data['aviatRfFreqTx'],
                1,
                1000
            );
        }

        return $sensors;
    }

    /**
     * Discover wireless tx or rx capacity. This is in bps. Type is rate.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessRate()
    {
        $sensors = [];
        $name = $this->getCacheByIndex('entPhysicalName', 'ENTITY-MIB');

        $tx = snmpwalk_cache_oid($this->getDeviceArray(), 'aviatModemCurCapacityTx', [], 'AVIAT-MODEM-MIB');
        foreach ($tx as $index => $data) {
            $sensors[] = new WirelessSensor(
                WirelessSensorType::Rate,
                $this->getDeviceId(),
                ".1.3.6.1.4.1.2509.9.3.2.1.1.11.$index",
                'aviat-wtm-carrier-tx-rate',
                $index,
                "TX Capacity ({$name[$index]})",
                $data['aviatModemCurCapacityTx'],
                1000
            );
        }

        $rx = snmpwalk_cache_oid($this->getDeviceArray(), 'aviatModemCurCapacityRx', [], 'AVIAT-MODEM-MIB');
        foreach ($rx as $index => $data) {
            $sensors[] = new WirelessSensor(
                WirelessSensorType::Rate,
                $this->getDeviceId(),
                ".1.3.6.1.4.1.2509.9.3.2.1.1.12.$index",
                'aviat-wtm-carrier-rx-rate',
                $index,
                "RX Capacity ({$name[$index]})",
                $data['aviatModemCurCapacityRx'],
                1000
            );
        }

        return $sensors;
    }

    /**
     * Discover wireless tx or rx RSL. This is in dbm. Type is rssi.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessRssi()
    {
        $sensors = [];
        $name = $this->getCacheByIndex('entPhysicalName', 'ENTITY-MIB');
        $rsl = snmpwalk_cache_oid($this->getDeviceArray(), 'aviatRxPerformRslReadingCurrent', [], 'AVIAT-RXPERFORMANCE-MIB');

        foreach ($rsl as $index => $data) {
            $sensors[] = new WirelessSensor(
                WirelessSensorType::Rssi,
                $this->getDeviceId(),
                ".1.3.6.1.4.1.2509.9.15.2.2.1.4.$index",
                'aviat-wtm-carrier-rsl',
                $index,
                "RSL ({$name[$index]})",
                $data['aviatRxPerformRslReadingCurrent'],
                1,
                10
            );
        }

        return $sensors;
    }

    /**
     * Discover wireless SNR (CINR). This is in dbm. Type is snr.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessSnr()
    {
        $sensors = [];
        $name = $this->getCacheByIndex('entPhysicalName', 'ENTITY-MIB');
        $snr = snmpwalk_cache_oid($this->getDeviceArray(), 'aviatRxPerformCinrReadingCurrent', [], 'AVIAT-RXPERFORMANCE-EX-MIB');

        foreach ($snr as $index => $data) {
            $sensors[] = new WirelessSensor(
                WirelessSensorType::Snr,
                $this->getDeviceId(),
                ".1.3.6.1.4.1.2509.9.33.2.2.1.3.$index",
                'aviat-wtm-carrier-snr',
                $index,
                "SNR ({$name[$index]})",
                $data['aviatRxPerformCinrReadingCurrent'],
                1,
                10
            );
        }

        return $sensors;
    }

    /**
     * Discover wireless TX power. This is in dbm. Type is power.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessPower()
    {
        $sensors = [];
        $name = $this->getCacheByIndex('entPhysicalName', 'ENTITY-MIB');
        $power = snmpwalk_cache_oid($this->getDeviceArray(), 'aviatRxPerformTxpowReadingCurrent', [], 'AVIAT-RXPERFORMANCE-EX-MIB');

        foreach ($power as $index => $data) {
            $sensors[] = new WirelessSensor(
                WirelessSensorType::Power,
                $this->getDeviceId(),
                ".1.3.6.1.4.1.2509.9.33.2.2.1.7.$index",
                'aviat-wtm-carrier-txpower',
                $index,
                "TX Power ({$name[$index]})",
                $data['aviatRxPerformTxpowReadingCurrent'],
                1,
                10
            );
        }

        return $sensors;
    }
}
