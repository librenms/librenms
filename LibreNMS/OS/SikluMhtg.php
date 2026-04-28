<?php

/**
 * SikluMhtg.php
 *
 * Siklu MultiHaul Terragraph
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
 * @copyright  2026 Robert Derryberry
 * @author     Robert Derryberry <26973780+bdg-robert@users.noreply.github.com>
 */

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Enum\WirelessSensorType;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessErrorRatioDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRateDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRssiDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSnrDiscovery;
use LibreNMS\OS;
use SnmpQuery;

class SikluMhtg extends OS implements
    WirelessErrorRatioDiscovery,
    WirelessRateDiscovery,
    WirelessRssiDiscovery,
    WirelessSnrDiscovery
{
    /**
     * Discover wireless RSSI (Received Signal Strength Indicator). This is in dBm. Type is rssi.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return list<WirelessSensor>
     */
    public function discoverWirelessRssi()
    {
        $sensors = [];
        foreach ($this->getRemoteLinks() as $ifIndex => $name) {
            $oid = '.1.3.6.1.4.1.31926.35.1.1.3.1.1.7.' . $ifIndex; // RADIO-BRIDGE-MH-TG-MIB::rbTgRcLinksActiveRssi
            $sensors[] = new WirelessSensor(WirelessSensorType::Rssi, $this->getDeviceId(), $oid, 'siklu-mhtg', $ifIndex, "RSSI ($name)");
        }

        return $sensors;
    }

    /**
     * Discover wireless SNR. This is in dB. Type is snr.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return list<WirelessSensor>
     */
    public function discoverWirelessSnr()
    {
        $sensors = [];
        foreach ($this->getRemoteLinks() as $ifIndex => $name) {
            $oid = '.1.3.6.1.4.1.31926.35.1.1.3.1.1.8.' . $ifIndex; // RADIO-BRIDGE-MH-TG-MIB::rbTgRcLinksActiveSnr
            $sensors[] = new WirelessSensor(WirelessSensorType::Snr, $this->getDeviceId(), $oid, 'siklu-mhtg', $ifIndex, "SNR ($name)");
        }

        return $sensors;
    }

    /**
     * Discover wireless rate. This is in bps. Type is rate.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return list<WirelessSensor>
     */
    public function discoverWirelessRate()
    {
        $sensors = [];
        foreach ($this->getRemoteLinks() as $ifIndex => $name) {
            $rx_oid = '.1.3.6.1.4.1.31926.35.1.1.3.1.1.14.' . $ifIndex; // RADIO-BRIDGE-MH-TG-MIB::rbTgRcLinksActiveSpeedRx
            $tx_oid = '.1.3.6.1.4.1.31926.35.1.1.3.1.1.15.' . $ifIndex; // RADIO-BRIDGE-MH-TG-MIB::rbTgRcLinksActiveSpeedTx
            $sensors[] = new WirelessSensor(WirelessSensorType::Rate, $this->getDeviceId(), $rx_oid, 'siklu-mhtg', "rx.$ifIndex", "RX Rate ($name)", null, 1000000, 1);
            $sensors[] = new WirelessSensor(WirelessSensorType::Rate, $this->getDeviceId(), $tx_oid, 'siklu-mhtg', "tx.$ifIndex", "TX Rate ($name)", null, 1000000, 1);
        }

        return $sensors;
    }

    /**
     * Discover wireless bit/packet error ratio. This is in percent. Type is error-ratio.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return list<WirelessSensor>
     */
    public function discoverWirelessErrorRatio()
    {
        $sensors = [];
        foreach ($this->getRemoteLinks() as $ifIndex => $name) {
            $rx_oid = '.1.3.6.1.4.1.31926.35.1.1.3.1.1.11.' . $ifIndex; // RADIO-BRIDGE-MH-TG-MIB::rbTgRcLinksActiveRxPer
            $tx_oid = '.1.3.6.1.4.1.31926.35.1.1.3.1.1.12.' . $ifIndex; // RADIO-BRIDGE-MH-TG-MIB::rbTgRcLinksActiveTxPer
            $sensors[] = new WirelessSensor(WirelessSensorType::ErrorRatio, $this->getDeviceId(), $rx_oid, 'siklu-mhtg', "rx.$ifIndex", "RX PER ($name)");
            $sensors[] = new WirelessSensor(WirelessSensorType::ErrorRatio, $this->getDeviceId(), $tx_oid, 'siklu-mhtg', "tx.$ifIndex", "TX PER ($name)");
        }

        return $sensors;
    }

    /**
     * @return array<int, string>
     */
    private function getRemoteLinks()
    {
        $oids = SnmpQuery::walk('RADIO-BRIDGE-MH-TG-MIB::rbTgRcLinksActiveRemoteName')->table(1);
        $links = [];
        foreach ($oids as $ifIndex => $entry) {
            $links[$ifIndex] = $entry['RADIO-BRIDGE-MH-TG-MIB::rbTgRcLinksActiveRemoteName'];
        }

        return $links;
    }
}
