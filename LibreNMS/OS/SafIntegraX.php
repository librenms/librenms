<?php
/**
 * Saf-IntegraX.php
 *
 * Saf Integra-X wireless radios
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
 * @copyright  2017 Neil Lathwood
 * @author     Neil Lathwood <gh+n@laf.io>
 */

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessMseDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessPowerDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRateDiscovery;
use LibreNMS\OS;

class SafIntegraX extends OS implements
    WirelessFrequencyDiscovery,
    WirelessMseDiscovery,
    WirelessPowerDiscovery,
    WirelessRateDiscovery
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
            // SAF-INTEGRAX-MIB::integraXradioAtxFrequency
            new WirelessSensor(
                'frequency',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.7571.100.1.1.7.10.2.1.2.0',
                'saf-integrax-a-tx',
                'integraXradioAtxFrequency',
                'Radio-A Tx Frequency',
                null,
                1,
                1000
            ),
            // SAF-INTEGRAX-MIB::integraXradioBtxFrequency
            new WirelessSensor(
                'frequency',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.7571.100.1.1.7.10.2.2.2.0',
                'saf-integrax-b-tx',
                'integraXradioBtxFrequency',
                'Radio-B Tx Frequency',
                null,
                1,
                1000
            ),
            // SAF-INTEGRAX-MIB::integraXradioArxFrequency
            new WirelessSensor(
                'frequency',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.7571.100.1.1.7.10.2.1.9.0',
                'saf-integrax-a-rx',
                'integraXradioArxFrequency',
                'Radio-A Rx Frequency',
                null,
                1,
                1000
            ),
            // SAF-INTEGRAX-MIB::integraXradioBrxFrequency
            new WirelessSensor(
                'frequency',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.7571.100.1.1.7.10.2.2.9.0',
                'saf-integrax-b-rx',
                'integraXradioBrxFrequency',
                'Radio-B Rx Frequency',
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
            // SAF-INTEGRAX-MIB::integraXmodemAnormalizedMse
            new WirelessSensor(
                'mse',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.7571.100.1.1.7.10.3.1.2.0',
                'saf-integrax-a-mse',
                'integraXmodemAnormalizedMse',
                'Modem-A MSE',
                null,
                1,
                10
            ),
            // SAF-INTEGRAX-MIB::integraXmodemBnormalizedMse
            new WirelessSensor(
                'mse',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.7571.100.1.1.7.10.3.2.2.0',
                'saf-integrax-b-mse',
                'integraXmodemBnormalizedMse',
                'Modem-B MSE',
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
            // SAF-INTEGRAX-MIB::integraXradioAtxPower
            new WirelessSensor(
                'power',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.7571.100.1.1.7.10.2.1.1.0',
                'saf-integrax-a-tx-power',
                'integraXradioAtxPower',
                'Radio-A Tx Power'
            ),
            // SAF-INTEGRAX-MIB::integraXradioBtxPower
            new WirelessSensor(
                'power',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.7571.100.1.1.7.10.2.2.1.0',
                'saf-integrax-b-tx-power',
                'integraXradioBtxPower',
                'Radio-B Tx Power'
            ),
            // SAF-INTEGRAX-MIB::integraXradioArxLevel
            new WirelessSensor(
                'power',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.7571.100.1.1.7.10.2.1.3.0',
                'saf-integrax-a-rx-level',
                'integraXradioArxLevel',
                'Radio-A Rx Level'
            ),
            // SAF-INTEGRAX-MIB::integraXradioBrxLevel
            new WirelessSensor(
                'power',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.7571.100.1.1.7.10.2.2.3.0',
                'saf-integrax-b-rx-level',
                'integraXradioBrxLevel',
                'Radio-B Rx Level'
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
            // SAF-INTEGRAX-MIB::integraXmodemArxCapacity
            new WirelessSensor(
                'rate',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.7571.100.1.1.7.10.3.1.9.0',
                'saf-integrax-a-rx-capacity',
                'integraXmodemArxCapacity',
                'Modem-A RX Capacity',
                null,
                1000
            ),
            // SAF-INTEGRAX-MIB::integraXmodemBrxCapacity
            new WirelessSensor(
                'rate',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.7571.100.1.1.7.10.3.2.9.0',
                'saf-integrax-b-rx-capacity',
                'integraXmodemBrxCapacity',
                'Modem-B RX Capacity',
                null,
                1000
            ),
            // SAF-INTEGRAX-MIB::integraXmodemAtxCapacity
            new WirelessSensor(
                'rate',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.7571.100.1.1.7.10.3.1.10.0',
                'saf-integrax-a-tx-capacity',
                'integraXmodemAtxCapacity',
                'Modem-A TX Capacity',
                null,
                1000
            ),
            // SAF-INTEGRAX-MIB::integraXmodemBtxCapacity
            new WirelessSensor(
                'rate',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.7571.100.1.1.7.10.3.2.10.0',
                'saf-integrax-b-tx-capacity',
                'integraXmodemBtxCapacity',
                'Modem-B TX Capacity',
                null,
                1000
            ),
        ];
    }
}
