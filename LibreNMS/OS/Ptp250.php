<?php
/**
 * Ptp250.php
 *
 * Cambium
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
 * @copyright  2017 Paul Heinrichs
 * @author     Paul Heinrichs<pdheinrichs@gmail.com>
 */

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessNoiseFloorDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessPowerDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRateDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSsrDiscovery;
use LibreNMS\OS;

class Ptp250 extends OS implements
    WirelessPowerDiscovery,
    WirelessNoiseFloorDiscovery,
    WirelessRateDiscovery,
    WirelessSsrDiscovery
{
    /**
     * Discover wireless tx or rx power. This is in dBm. Type is power.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessPower()
    {
        $transmit = '.1.3.6.1.4.1.17713.250.5.3.0'; //"CAMBIUM-PTP250-MIB::transmitPower.0"
        $receive = '.1.3.6.1.4.1.17713.250.5.1.0'; //"CAMBIUM-PTP250-MIB::receivePower.0";

        return [
            new WirelessSensor(
                'power',
                $this->getDeviceId(),
                $transmit,
                'ptp250-tx',
                0,
                'PTP250 Transmit',
                null,
                1,
                10
            ),
            new WirelessSensor(
                'power',
                $this->getDeviceId(),
                $receive,
                'ptp250-rx',
                0,
                'PTP250 Receive',
                null,
                1,
                10
            ),
        ];
    }

    /**
     * Discover wireless noise floor. This is in dBm/Hz. Type is noise-floor.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessNoiseFloor()
    {
        $noise_floor = '.1.3.6.1.4.1.17713.250.5.15.0'; //"CAMBIUM-PTP250-MIB::noiseFloor.0";;

        return [
            new WirelessSensor(
                'noise-floor',
                $this->getDeviceId(),
                $noise_floor,
                'ptp250',
                0,
                'PTP250 Noise Floor',
                null
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
        $receive = '.1.3.6.1.4.1.17713.250.11.1.0'; //"CAMBIUM-PTP250-MIB::receiveDataRate.0"
        $transmit = '.1.3.6.1.4.1.17713.250.11.2.0'; //"CAMBIUM-PTP250-MIB::transmitDataRate.0"
        $aggregate = '.1.3.6.1.4.1.17713.250.11.3.0'; //"CAMBIUM-PTP250-MIB::aggregateDataRate.0"
        $txModulation = '.1.3.6.1.4.1.17713.250.5.9.0';
        $rxModulation = '.1.3.6.1.4.1.17713.250.5.8.0';

        return [
            new WirelessSensor(
                'rate',
                $this->getDeviceId(),
                $receive,
                'ptp250-rx-rate',
                0,
                'PTP250 Receive Rate',
                null,
                1000,
                1
            ),
            new WirelessSensor(
                'rate',
                $this->getDeviceId(),
                $transmit,
                'ptp250-tx-rate',
                0,
                'PTP250 Transmit Rate',
                null,
                1000,
                1
            ),
            new WirelessSensor(
                'rate',
                $this->getDeviceId(),
                $aggregate,
                'ptp250-ag-rate',
                0,
                'PTP250 Aggregate Rate',
                null,
                1000,
                1
            ),
            new WirelessSensor(
                'rate',
                $this->getDeviceId(),
                $txModulation,
                'ptp250-tx-mod',
                0,
                'PTP250 Transmit Modulation Rate',
                null
            ),
            new WirelessSensor(
                'rate',
                $this->getDeviceId(),
                $rxModulation,
                'ptp250-rx-mod',
                0,
                'PTP250 Receive Modulation Rate',
                null
            ),
        ];
    }

    /**
     * Discover wireless SSR.  This is in dB. Type is ssr.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessSsr()
    {
        $ssr = '.1.3.6.1.4.1.17713.250.5.13.0'; // CAMBIUM-PTP250-MIB::signalStrengthRatio.0

        return [
            new WirelessSensor(
                'ssr',
                $this->getDeviceId(),
                $ssr,
                'ptp250',
                0,
                'PTP250 Signal Strength Ratio',
                null,
                1,
                10
            ),
        ];
    }
}
