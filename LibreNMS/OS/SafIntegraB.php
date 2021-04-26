<?php
/**
 * Saf-Integra.php
 *
 * Saf Integra wireless radios
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
 * @copyright  2017 Neil Lathwood
 * @author     Neil Lathwood <gh+n@laf.io>
 */

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessMseDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessPowerDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessQualityDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRateDiscovery;
use LibreNMS\OS;

class SafIntegraB extends OS implements
    WirelessFrequencyDiscovery,
    WirelessMseDiscovery,
    WirelessPowerDiscovery,
    WirelessRateDiscovery,
    WirelessQualityDiscovery
{
    /**
     * Discover wireless frequency.  This is in MHz. Type is frequency.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessFrequency()
    {
        return [
            // SAF-INTEGRAB-MIB::integraBradioTxFrequency
            new WirelessSensor(
                'frequency',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.7571.100.1.1.7.1.2.2.0',
                'saf-integrab-tx',
                'integraBradioTxFrequency',
                'Tx Frequency',
                null,
                1,
                1000
            ),
            // SAF-INTEGRAB-MIB::integraBradioRxFrequency
            new WirelessSensor(
                'frequency',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.7571.100.1.1.7.1.2.7.0',
                'saf-integrab-rx',
                'integraBradioRxFrequency',
                'Rx Frequency',
                null,
                1,
                1000
            ),
        ];
    }

    /**
     * Discover wireless MSE. Mean square error value *10 in dB.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessMse()
    {
        return [
            // SAF-INTEGRAB-MIB::integraBmodemMse
            new WirelessSensor(
                'mse',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.7571.100.1.1.7.1.3.2.0',
                'saf-integrab-modem',
                'integraBmodemMse',
                'Modem MSE',
                null,
                1,
                10
            ),
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
            // SAF-INTEGRAB-MIB::integraBradioTxPower
            new WirelessSensor(
                'power',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.7571.100.1.1.7.1.2.1.0',
                'saf-integrab-tx',
                'integraBradioTxPower',
                'Tx Power'
            ),
            // SAF-INTEGRAB-MIB::integraBradioRxLevel
            new WirelessSensor(
                'power',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.7571.100.1.1.7.1.2.3.0',
                'saf-integrab-rx-level',
                'integraBradioRxLevel',
                'Rx Level'
            ),
        ];
    }

    /**
     * Discover wireless rate. This is in bps. Type is rate.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessRate()
    {
        return [
            // SAF-INTEGRAB-MIB::integraBmodemRxCapacity
            new WirelessSensor(
                'rate',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.7571.100.1.1.7.1.3.10.0',
                'saf-integrab-rx',
                'integraBmodemRxCapacity',
                'RX Capacity',
                null,
                1000
            ),
            // SAF-INTEGRAB-MIB::integraBmodemTxCapacity
            new WirelessSensor(
                'rate',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.7571.100.1.1.7.1.3.11.0',
                'saf-integrab-tx',
                'integraBmodemTxCapacity',
                'TX Capacity',
                null,
                1000
            ),
        ];
    }

    /**
     * Discover wireless quality.  This is a percent. Type is quality.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessQuality()
    {
        return [
            // SAF-INTEGRAB-MIB::integraBmodemSignalQuality
            new WirelessSensor(
                'quality',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.7571.100.1.1.7.1.3.14.0',
                'saf-integrab-quality',
                'integraBmodemSignalQuality',
                'Model Signal',
                null,
                1
            ),
        ];
    }
}
