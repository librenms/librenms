<?php
/**
 * Trinity.php
 *
 * Repeatit Trinity Series
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
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessPowerDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRateDiscovery;
use LibreNMS\OS;

class Trinity extends OS implements
    WirelessFrequencyDiscovery,
    WirelessPowerDiscovery,
    WirelessRateDiscovery
{
    private $data;

    /**
     * Discover wireless distance.  This is in Kilometers. Type is distance.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    /** Not supportet atm by Repeatit MIB
    public function discoverWirelessDistance()

    /**
     * Discover wireless frequency.  This is in GHz. Type is frequency.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessFrequency()
    {
        $oid = '.1.3.6.1.4.1.16068.3.2.2.1.6.0'; // REPEATIT-MIB::rfOperationalFrequency
        return array(
            new WirelessSensor('frequency', $this->getDeviceId(), $oid, 'trinity', 1, 'Radio Frequency', null, 1, 1000000),
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
        $tx_oid = '.1.3.6.1.4.1.16068.3.2.2.1.15.0'; // REPEATIT-MIB::rfTxPower
        $rx_oid = '.1.3.6.1.4.1.16068.3.2.2.1.11.0'; // REPEATIT-MIB::rfSignCh1Ctrl
        $rx_oid1 = '.1.3.6.1.4.1.16068.3.2.2.1.12.0'; // REPEATIT-MIB::rfSignCh1Ctrl

        return array(
            new WirelessSensor('power', $this->getDeviceId(), $tx_oid, 'trinity-tx', 1, 'Tx Power'),
            new WirelessSensor('power', $this->getDeviceId(), $rx_oid, 'trinity-rx', 0, 'Rx Power Ch1'),
            new WirelessSensor('power', $this->getDeviceId(), $rx_oid1, 'trinity-rx1', 0, 'Rx Power Ch2'),
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
                '.1.3.6.1.4.1.16068.3.2.3.1.12.' . $index,
                'trinity-tx',
                $index,
                'SSID: ' . $entry['rfLinkId'] . ' Tx',
                $entry['linkTxRate'],
                100000,
                1
            );
            $sensors[] = new WirelessSensor(
                'rate',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.16068.3.2.3.1.13.' . $index,
                'trinity-rx',
                $index,
                'SSID: ' . $entry['rfLinkId'] . ' Rx',
                $entry['linkRxRate'],
                100000,
                1
            );
        }
        return $sensors;
    }
    private function fetchData()
    {
        if (is_null($this->data)) {
            $this->data = snmpwalk_cache_oid($this->getDevice(), 'rfLinkId', array(), 'REPEATIT-MIB');
        }
        return $this->data;
    }
}
