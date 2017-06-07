<?php
/**
 * Fibeair.php
 *
 * Ceragon FibeAir 2000 Family
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
use LibreNMS\Interfaces\Discovery\Sensors\WirelessDistanceDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessPowerDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRateDiscovery;
use LibreNMS\OS;

class Fibeair extends OS implements
    WirelessFrequencyDiscovery,
    WirelessPowerDiscovery,
    WirelessDistanceDiscovery,
    WirelessRateDiscovery
{

    private $data;
    /**
     * Discover wireless distance.  This is in Kilometers. Type is distance.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessDistance()
    {
        $oid = '.1.3.6.1.4.1.4458.1000.1.5.29.0'; // CERAGON-NET-MIB-FIBEAIR-4800::fibeAir4800OduAirLinkDistance
        return array(
            new WirelessSensor('distance', $this->getDeviceId(), $oid, 'fibeair', 1, 'Distance', null, 1, 1000)
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
        $oid = '.1.3.6.1.4.1.4458.1000.1.5.16.0'; // CERAGON-NET-MIB-FIBEAIR-4800::fibeAir4800OduAirCurrentFreq
        return array(
            new WirelessSensor('frequency', $this->getDeviceId(), $oid, 'fibeair-freq', 1, 'Frequency'),
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
        $tx_oid = '.1.3.6.1.4.1.4458.1000.1.5.4.0'; // CERAGON-NET-MIB-FIBEAIR-4800::fibeAir4800OduAirTxPower
        $rx_oid = '.1.3.6.1.4.1.4458.1000.1.5.9.1.0'; // CERAGON-NET-MIB-FIBEAIR-4800::fibeAir4800OduAirRxPower

        return array(
            new WirelessSensor('power', $this->getDeviceId(), $tx_oid, 'fibeair-tx', 1, 'Tx Power'),
            new WirelessSensor('power', $this->getDeviceId(), $rx_oid, 'fibeair-rx', 0, 'Rx Power'),
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
        $data = $this->fetchData();
        $sensors = array();
        foreach ($data as $index => $entry) {
            $sensors[] = new WirelessSensor(
                'rate',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.4458.1000.1.5.70.' . $index,
                'fibeair-cap',
                $index,
                'SSID: ' . $entry['fibeAir4800OduAirSSID'] . ' Tx',
                $entry['fibeAir4800OduAirAggregateCapacity'],
                1000000,
                1
            );
        }
        return $sensors;
    }
    private function fetchData()
    {
        if (is_null($this->data)) {
            $this->data = snmpwalk_cache_oid($this->getDevice(), 'fibeAir4800OduAirSSID', array(), 'CERAGON-NET-MIB-FIBEAIR-4800');
        }
        return $this->data;
    }
}
