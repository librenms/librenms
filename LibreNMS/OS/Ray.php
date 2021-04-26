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
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use LibreNMS\Device\Processor;
use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\ProcessorDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessPowerDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRateDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRssiDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSnrDiscovery;
use LibreNMS\OS;

class Ray extends OS implements
    ProcessorDiscovery,
    WirelessFrequencyDiscovery,
    WirelessPowerDiscovery,
    WirelessRssiDiscovery,
    WirelessRateDiscovery,
    WirelessSnrDiscovery
{
    /**
     * Discover processors.
     * Returns an array of LibreNMS\Device\Processor objects that have been discovered
     *
     * @return array Processors
     */
    public function discoverProcessors()
    {
        // RAY-MIB::useCpu has no index, so it won't work in yaml

        return [
            Processor::discover(
                $this->getName(),
                $this->getDeviceId(),
                '.1.3.6.1.4.1.33555.1.1.5.1',
                0
            ),
        ];
    }

    /**
     * Discover wireless frequency.  This is in GHz. Type is frequency.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessFrequency()
    {
        return [
            // RAY-MIB::txFreq.0
            new WirelessSensor('frequency', $this->getDeviceId(), '.1.3.6.1.4.1.33555.1.2.1.4', 'racom-tx', 1, 'TX Frequency', null, 1, 1000),
            // RAY-MIB::rxFreq.0
            new WirelessSensor('frequency', $this->getDeviceId(), '.1.3.6.1.4.1.33555.1.2.1.3', 'racom-rx', 1, 'RX Frequency', null, 1, 1000),
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
            new WirelessSensor('power', $this->getDeviceId(), '.1.3.6.1.4.1.33555.1.2.1.17', 'racom-pow-cur', 1, 'Tx Power Current'),
            //RAY-MIB::rfPowerConfigured.0
            new WirelessSensor('power', $this->getDeviceId(), '.1.3.6.1.4.1.33555.1.2.1.12', 'racom-pow-conf', 1, 'Tx Power Configured'),
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
        $oid = '.1.3.6.1.4.1.33555.1.3.2.1'; // RAY-MIB::rss.0

        return [
            new WirelessSensor('rssi', $this->getDeviceId(), $oid, 'racom', 1, 'RSSI', null, 1, 10),
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
        $oid = '.1.3.6.1.4.1.33555.1.3.2.2'; // RAY-MIB::snr.0

        return [
            new WirelessSensor('snr', $this->getDeviceId(), $oid, 'racom', 1, 'CINR', null, 1, 10),
        ];
    }

    /**
     * Discover wireless RATE.  This is in bps. Type is rate.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessRate()
    {
        $oid_bitrate = '.1.3.6.1.4.1.33555.1.2.1.13'; // RAY-MIB::netBitrate.0
        $oid_maxbitrate = '.1.3.6.1.4.1.33555.1.2.1.14'; // RAY-MIB::maxNetBitrate.0

        return [
            new WirelessSensor('rate', $this->getDeviceId(), $oid_bitrate, 'racom-netBitrate', 1, 'Net Bitrate', null, 1000, 1),
            new WirelessSensor('rate', $this->getDeviceId(), $oid_maxbitrate, 'racom-maxNetBitrate', 2, 'Max Net Bitrate', null, 1000, 1),
        ];
    }
}
