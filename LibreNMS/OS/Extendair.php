<?php
/**
 * Extendair.php
 *
 * Exalt ExtendAir
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

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessErrorRatioDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessPowerDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRateDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSignalDiscovery;
use LibreNMS\OS;

class Extendair extends OS implements
    WirelessErrorRatioDiscovery,
    WirelessFrequencyDiscovery,
    WirelessPowerDiscovery,
    WirelessRateDiscovery,
    WirelessSignalDiscovery
{
    /**
     * Discover wireless bit/packet error ratio.  This is in percent. Type is error-ratio.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessErrorRatio()
    {
        $oid = '.1.3.6.1.4.1.25651.1.2.4.3.2.1.0'; // ExaltComProducts::remCurrentBER.0
        return array(
            new WirelessSensor('error-ratio', $this->getDeviceId(), $oid, 'extendair', 0, 'Bit Error Ratio'),
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
        $tx_oid = '.1.3.6.1.4.1.25651.1.2.3.1.57.4.0'; // ExtendAirG2::extendAirG2TXfrequency.0
        $rx_oid = '.1.3.6.1.4.1.25651.1.2.3.1.57.5.0'; // ExtendAirG2::extendAirG2RXfrequency.0
        return array(
            new WirelessSensor(
                'frequency',
                $this->getDeviceId(),
                $tx_oid,
                'extendair',
                'tx',
                'Tx Frequency',
                null,
                1,
                1000
            ),
            new WirelessSensor(
                'frequency',
                $this->getDeviceId(),
                $rx_oid,
                'extendair',
                'rx',
                'Rx Frequency',
                null,
                1,
                1000
            ),
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
        $oid = '.1.3.6.1.4.1.25651.1.2.3.1.57.1.0'; // ExtendAirG2::extendAirG2TxPower.0
        return array(
            new WirelessSensor('power', $this->getDeviceId(), $oid, 'extendair', 0, 'Tx Power', null, 1, 10),
        );
    }

    /**
     * Discover wireless rate. This is in Mbps. Type is rate.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessRate()
    {
        $oid = '.1.3.6.1.4.1.25651.1.2.4.5.1.0'; // ExaltComProducts::aggregateUserThroughput.0
        return array(
            new WirelessSensor(
                'rate',
                $this->getDeviceId(),
                $oid,
                'extendair',
                0,
                'Aggregate User Throughput',
                null,
                1048576
            ),
        );
    }

    /**
     * Discover wireless signal strength. This is in dBm. Type is signal.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessSignal()
    {
        $oid = '.1.3.6.1.4.1.25651.1.2.4.3.2.3.0'; // ExaltComProducts::remCurrentRSL.0
        $min_oid = '.1.3.6.1.4.1.25651.1.2.4.3.1.9.0'; // ExaltComProducts::locMinRSL.0
        $max_oid = '.1.3.6.1.4.1.25651.1.2.4.3.1.12.0'; // ExaltComProducts::locMaxRSL.0
        return array(
            new WirelessSensor('signal', $this->getDeviceId(), $oid, 'extendair', 0, 'Current Signal'),
            new WirelessSensor('signal', $this->getDeviceId(), $min_oid, 'extendair', 'min', 'Min Signal'),
            new WirelessSensor('signal', $this->getDeviceId(), $max_oid, 'extendair', 'max', 'Max Signal'),
        );
    }
}
