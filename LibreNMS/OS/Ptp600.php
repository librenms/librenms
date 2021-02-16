<?php
/**
 * ptp600.php
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
use LibreNMS\Interfaces\Discovery\Sensors\WirelessPowerDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRateDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSsrDiscovery;
use LibreNMS\OS;

class Ptp600 extends OS implements
    WirelessPowerDiscovery,
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
        $transmit = '.1.3.6.1.4.1.17713.6.12.3.0'; //"CAMBIUM-PTP600-MIB::transmitPower.0"
        $receive = '.1.3.6.1.4.1.17713.6.12.1.0'; //"CAMBIUM-PTP600-MIB::receivePower.0";

        return [
            new WirelessSensor(
                'power',
                $this->getDeviceId(),
                $transmit,
                'ptp600-tx',
                0,
                'PTP600 Transmit',
                null,
                1,
                10
            ),
            new WirelessSensor(
                'power',
                $this->getDeviceId(),
                $receive,
                'ptp600-rx',
                0,
                'PTP600 Receive',
                null,
                1,
                10
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
        $receive = '.1.3.6.1.4.1.17713.6.20.1.0'; //"CAMBIUM-PTP600-MIB::receiveDataRate.0"
        $transmit = '.1.3.6.1.4.1.17713.6.20.2.0'; //"CAMBIUM-PTP600-MIB::transmitDataRate.0"
        $aggregate = '.1.3.6.1.4.1.17713.6.20.3.0'; //"CAMBIUM-PTP600-MIB::aggregateDataRate.0"

        return [
            new WirelessSensor(
                'rate',
                $this->getDeviceId(),
                $receive,
                'ptp600-rx-rate',
                0,
                'PTP600 Receive Rate',
                null,
                1000,
                1
            ),
            new WirelessSensor(
                'rate',
                $this->getDeviceId(),
                $transmit,
                'ptp600-tx-rate',
                0,
                'PTP600 Transmit Rate',
                null,
                1000,
                1
            ),
            new WirelessSensor(
                'rate',
                $this->getDeviceId(),
                $aggregate,
                'ptp600-ag-rate',
                0,
                'PTP600 Aggregate Rate',
                null,
                1000,
                1
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
        $ssr = '.1.3.6.1.4.1.17713.6.12.13.0'; // CAMBIUM-PTP600-MIB::signalStrengthRatio.0

        return [
            new WirelessSensor(
                'ssr',
                $this->getDeviceId(),
                $ssr,
                'ptp600-ssr',
                0,
                'PTP600 Signal Strength Ratio',
                null,
                1,
                10
            ),
        ];
    }
}
