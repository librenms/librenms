<?php
/**
 * SafCfml4.php
 *
 * Saf CFM wireless radios
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
 * @copyright  2018 Janno Schouwenburg
 * @author     Janno Schouwenburg <handel@janno.nl>
 */

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessErrorsDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessPowerDiscovery;
use LibreNMS\OS;

class SafCfm extends OS implements
    WirelessFrequencyDiscovery,
    WirelessPowerDiscovery,
    WirelessErrorsDiscovery
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
            // SAF-MPMUX-MIB::cfml4radioTxFrequency
            new WirelessSensor(
                'frequency',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.7571.100.1.1.2.22.3.6.0',
                'saf-cfml4-tx',
                'cfml4radioR1TxFrequency',
                'Radio 1 Tx Frequency'
            ),
            // SAF-MPMUX-MIB::cfml4radioRxFrequency
            new WirelessSensor(
                'frequency',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.7571.100.1.1.2.22.3.7.0',
                'saf-cfml4-rx',
                'cfml4radioR1RxFrequency',
                'Radio 1 Rx Frequency'
            ),
            new WirelessSensor(
                'frequency',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.7571.100.1.1.2.22.4.6.0',
                'saf-cfml4-tx',
                'cfml4radioR2TxFrequency',
                'Radio 2 Tx Frequency'
            ),
            // SAF-MPMUX-MIB::cfml4radioRxFrequency
            new WirelessSensor(
                'frequency',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.7571.100.1.1.2.22.4.7.0',
                'saf-cfml4-rx',
                'cfml4radioR2RxFrequency',
                'Radio 2 Rx Frequency'
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
            // SAF-MPMUX-MIB::rf1TxLevel
            new WirelessSensor(
                'power',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.7571.100.1.1.2.22.3.8.0',
                'saf-cfml4-tx-power',
                'cfml4radioR1TxPower',
                'Radio 1 Tx Power'
            ),
            // SAF-MPMUX-MIB::rf1RxLevel
            new WirelessSensor(
                'power',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.7571.100.1.1.2.22.3.10.0',
                'saf-cfml4-rx-level',
                'cfml4radioR1RxLevel',
                'Radio 1 Rx Level'
            ),
            // SAF-MPMUX-MIB::rf2TxLevel
            new WirelessSensor(
                'power',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.7571.100.1.1.2.22.4.8.0',
                'saf-cfml4-tx-power',
                'cfml4radioR2TxPower',
                'Radio 2 Tx Power'
            ),
            // SAF-MPMUX-MIB::rf2RxLevel
            new WirelessSensor(
                'power',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.7571.100.1.1.2.22.4.10.0',
                'saf-cfml4-rx-level',
                'cfml4radioR2RxLevel',
                'Radio 2 Rx Level'
            ),
        ];
    }

    /**
     * Discover wireless tx or rx power. This is in dBm. Type is power.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessErrors()
    {
        return [
            // SAF-MPMUX-MIB::termFrameErrors
            new WirelessSensor(
                'errors',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.7571.100.1.1.2.22.1.10.0',
                'saf-cfml4',
                'cfml4termFrameErrors',
                'Frame errors'
            ),
            // SAF-MPMUX-MIB::termBFrameErr
            new WirelessSensor(
                'errors',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.7571.100.1.1.2.22.1.29.0',
                'saf-cfml4',
                'cfml4termBFrameErr',
                'Background Frame errors'
            ),
        ];
    }
}
