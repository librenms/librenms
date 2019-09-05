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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use LibreNMS\Device\Processor;
use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\ProcessorDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessPowerDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRssiDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSnrDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRateDiscovery;
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

        return array(
            Processor::discover(
                $this->getName(),
                $this->getDeviceId(),
                '.1.3.6.1.4.1.33555.1.1.5.1.0',
                0
            ),
            Processor::discover(
                $this->getName(),
                $this->getDeviceId(),
                '.1.3.6.1.4.1.33555.1.1.5.1',
                0
            )

        );
    }

    /**
     * Discover wireless frequency.  This is in GHz. Type is frequency.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessFrequency()
    {
        return array(
            // RAY-MIB::txFreq.0
            new WirelessSensor('frequency', $this->getDeviceId(), '.1.3.6.1.4.1.33555.1.2.1.4.0', 'racom-tx', 1, 'TX Frequency', null, 1, 1000),
            // RAY-MIB::rxFreq.0
            new WirelessSensor('frequency', $this->getDeviceId(), '.1.3.6.1.4.1.33555.1.2.1.3.0', 'racom-rx', 1, 'RX Frequency', null, 1, 1000),
         // RAY-MIB::txFreq.0
            new WirelessSensor('frequency', $this->getDeviceId(), '.1.3.6.1.4.1.33555.1.2.1.4', 'racom-tx-old', 1, 'TX Frequency', null, 1, 1000),
            // RAY-MIB::rxFreq.0
            new WirelessSensor('frequency', $this->getDeviceId(), '.1.3.6.1.4.1.33555.1.2.1.3', 'racom-rx-old', 1, 'RX Frequency', null, 1, 1000),

        );
    }

    /**
     * Discover wireless tx or rx power. This is in dBm. Type is power.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessPower()
    {
        return array(
            // RAY-MIB::rfPowerCurrent.0
            new WirelessSensor('power', $this->getDeviceId(), '.1.3.6.1.4.1.33555.1.2.1.17.0', 'racom-pow-cur', 1, 'Tx Power Current'),
            //RAY-MIB::rfPowerConfigured.0
            new WirelessSensor('power', $this->getDeviceId(), '.1.3.6.1.4.1.33555.1.2.1.12.0', 'racom-pow-conf', 1, 'Tx Power Configured'),
            // RAY-MIB::rfPowerCurrent.0
            new WirelessSensor('power', $this->getDeviceId(), '.1.3.6.1.4.1.33555.1.2.1.17', 'racom-pow-cur-old', 1, 'Tx Power Current'),
            //RAY-MIB::rfPowerConfigured.0
            new WirelessSensor('power', $this->getDeviceId(), '.1.3.6.1.4.1.33555.1.2.1.12', 'racom-pow-conf-old', 1, 'Tx Power Configured'),


        );
    }

    /**
     * Discover wireless RSSI (Received Signal Strength Indicator). This is in dBm. Type is rssi.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessRssi()
    {
        return array(
            new WirelessSensor('rssi', $this->getDeviceId(), '.1.3.6.1.4.1.33555.1.3.2.1.0', 'racom', 1, 'RSSI', null, 1, 10),
            new WirelessSensor('rssi', $this->getDeviceId(), '.1.3.6.1.4.1.33555.1.3.2.1', 'racom-old', 1, 'RSSI', null, 1, 10),

        );
    }

    /**
     * Discover wireless SNR.  This is in dB. Type is snr.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessSnr()
    {
        return array(
            new WirelessSensor('snr', $this->getDeviceId(), '.1.3.6.1.4.1.33555.1.3.2.2.0', 'racom', 1, 'CINR', null, 1, 10),
            new WirelessSensor('snr', $this->getDeviceId(), '.1.3.6.1.4.1.33555.1.3.2.2', 'racom-old', 1, 'CINR', null, 1, 10),

        );
    }
    /**
     * Discover wireless RATE.  This is in bps. Type is rate.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessRate()
    {
        return array(
            new WirelessSensor('rate', $this->getDeviceId(), '.1.3.6.1.4.1.33555.1.2.1.13.0', 'racom-netBitrate', 1, 'Net Bitrate', null, 1000, 1),
            new WirelessSensor('rate', $this->getDeviceId(), '.1.3.6.1.4.1.33555.1.2.1.14.0', 'racom-maxNetBitrate', 2, 'Max Net Bitrate', null, 1000000, 1),
            new WirelessSensor('rate', $this->getDeviceId(), '.1.3.6.1.4.1.33555.1.2.1.13', 'racom-netBitrate-old', 1, 'Net Bitrate', null, 1000, 1),
            new WirelessSensor('rate', $this->getDeviceId(), '.1.3.6.1.4.1.33555.1.2.1.14', 'racom-maxNetBitrate-old', 2, 'Max Net Bitrate', null, 1000000, 1),

        );
    }
}
