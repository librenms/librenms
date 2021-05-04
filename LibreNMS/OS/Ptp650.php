<?php
/**
 * ptp650.php
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

class Ptp650 extends OS implements
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
        $transmit = '.1.3.6.1.4.1.17713.7.12.4.0'; //CAMBIUM-PTP650-MIB::transmitPower.0
        $receive = '.1.3.6.1.4.1.17713.7.12.12.0'; //CAMBIUM-PTP650ptp650-MIB::rawReceivePower.0

        return [
            new WirelessSensor(
                'power',
                $this->getDeviceId(),
                $transmit,
                'ptp650-tx',
                0,
                'ptp650 Transmit',
                null,
                1,
                10
            ),
            new WirelessSensor(
                'power',
                $this->getDeviceId(),
                $receive,
                'ptp650-rx',
                0,
                'ptp650 Receive',
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
        $receive = '.1.3.6.1.4.1.17713.7.20.1.0'; //CAMBIUM-PTP650-MIB::receiveDataRate.0
        $transmit = '.1.3.6.1.4.1.17713.7.20.2.0'; //CAMBIUM-PTP650-MIB::transmitDataRate.0
        $aggregate = '.1.3.6.1.4.1.17713.7.20.3.0'; //CAMBIUM-PTP650-MIB::aggregateDataRate.0
        $txModulation = '.1.3.6.1.4.1.17713.7.12.15.0';
        $rxModulation = '.1.3.6.1.4.1.17713.7.12.14.0';

        return [
            new WirelessSensor(
                'rate',
                $this->getDeviceId(),
                $receive,
                'ptp650-rx-rate',
                0,
                'PTP650 Receive Rate',
                null,
                1000,
                1
            ),
            new WirelessSensor(
                'rate',
                $this->getDeviceId(),
                $transmit,
                'ptp650-tx-rate',
                0,
                'PTP650 Transmit Rate',
                null,
                1000,
                1
            ),
            new WirelessSensor(
                'rate',
                $this->getDeviceId(),
                $aggregate,
                'ptp650-ag-rate',
                0,
                'PTP650 Aggregate Rate',
                null,
                1000,
                1
            ),
            new WirelessSensor(
                'rate',
                $this->getDeviceId(),
                $txModulation,
                'ptp650-tx-mod',
                0,
                'PTP650 Transmit Modulation Rate',
                null
            ),
            new WirelessSensor(
                'rate',
                $this->getDeviceId(),
                $rxModulation,
                'ptp650-rx-mod',
                0,
                'PTP650 Receive Modulation Rate',
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
        $ssr = '.1.3.6.1.4.1.17713.7.12.9.0'; // CAMBIUM-PTP650-MIB::signalStrengthRatio.0

        return [
            new WirelessSensor(
                'ssr',
                $this->getDeviceId(),
                $ssr,
                'ptp650',
                0,
                'PTP650 Signal Strength Ratio',
                null,
                1,
                10
            ),
        ];
    }
}
