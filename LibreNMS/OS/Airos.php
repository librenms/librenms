<?php
/**
 * Airos.php
 *
 * Ubiquiti AirOS
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
use LibreNMS\Interfaces\Discovery\Sensors\WirelessCapacityDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessCcqDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessDistanceDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessNoiseFloorDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessPowerDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessQualityDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRateDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRssiDiscovery;
use LibreNMS\OS;

class Airos extends OS implements
    WirelessCapacityDiscovery,
    WirelessCcqDiscovery,
    WirelessClientsDiscovery,
    WirelessDistanceDiscovery,
    WirelessFrequencyDiscovery,
    WirelessNoiseFloorDiscovery,
    WirelessPowerDiscovery,
    WirelessQualityDiscovery,
    WirelessRateDiscovery,
    WirelessRssiDiscovery
{
    /**
     * Discover wireless frequency.  This is in Hz. Type is frequency.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessFrequency()
    {
        $oid = '.1.3.6.1.4.1.41112.1.4.1.1.4.1'; //UBNT-AirMAX-MIB::ubntRadioFreq.1
        return array(
            new WirelessSensor('frequency', $this->getDeviceId(), $oid, 'airos', 1, 'Radio Frequency'),
        );
    }

    /**
     * Discover wireless capacity.  This is a percent. Type is capacity.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessCapacity()
    {
        $oid = '.1.3.6.1.4.1.41112.1.4.6.1.4.1'; //UBNT-AirMAX-MIB::ubntAirMaxCapacity.1
        return array(
            new WirelessSensor('capacity', $this->getDeviceId(), $oid, 'airos', 1, 'airMAX Capacity'),
        );
    }

    /**
     * Discover wireless client connection quality.  This is a percent. Type is ccq.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessCcq()
    {
        $oid = '.1.3.6.1.4.1.41112.1.4.5.1.7.1'; //UBNT-AirMAX-MIB::ubntWlStatCcq.1
        return array(
            new WirelessSensor('ccq', $this->getDeviceId(), $oid, 'airos', 1, 'CCQ'),
        );
    }

    /**
     * Discover wireless client counts. Type is clients.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessClients()
    {
        $oid = '.1.3.6.1.4.1.41112.1.4.5.1.15.1'; //UBNT-AirMAX-MIB::ubntWlStatStaCount.1
        return array(
            new WirelessSensor('clients', $this->getDeviceId(), $oid, 'airos', 1, 'Clients'),
        );
    }

    /**
     * Discover wireless distance.  This is in kilometers. Type is distance.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessDistance()
    {
        $oid = '.1.3.6.1.4.1.41112.1.4.1.1.7.1'; //UBNT-AirMAX-MIB::ubntRadioDistance.1
        return array(
            new WirelessSensor('distance', $this->getDeviceId(), $oid, 'airos', 1, 'Distance', null, 1, 1000),
        );
    }

    /**
     * Discover wireless noise floor. This is in dBm/Hz. Type is noise-floor.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessNoiseFloor()
    {
        $oid = '.1.3.6.1.4.1.41112.1.4.5.1.8.1'; //UBNT-AirMAX-MIB::ubntWlStatNoiseFloor.1
        return array(
            new WirelessSensor('noise-floor', $this->getDeviceId(), $oid, 'airos', 1, 'Noise Floor'),
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
        $tx_oid = '.1.3.6.1.4.1.41112.1.4.1.1.6.1'; //UBNT-AirMAX-MIB::ubntRadioTxPower.1
        $rx_oid = '.1.3.6.1.4.1.41112.1.4.5.1.5.1'; //UBNT-AirMAX-MIB::ubntWlStatSignal.1
        return array(
            new WirelessSensor('power', $this->getDeviceId(), $tx_oid, 'airos-tx', 1, 'Tx Power'),
            new WirelessSensor('power', $this->getDeviceId(), $rx_oid, 'airos-rx', 1, 'Signal Level'),
        );
    }

    /**
     * Discover wireless quality.  This is a percent. Type is quality.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessQuality()
    {
        $oid = '.1.3.6.1.4.1.41112.1.4.6.1.3.1'; //UBNT-AirMAX-MIB::ubntAirMaxQuality.1
        return array(
            new WirelessSensor('quality', $this->getDeviceId(), $oid, 'airos', 1, 'airMAX Quality'),
        );
    }

    /**
     * Discover wireless rate. This is in bps. Type is rate.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessRate()
    {
        $tx_oid = '.1.3.6.1.4.1.41112.1.4.5.1.9.1'; //UBNT-AirMAX-MIB::ubntWlStatTxRate.1
        $rx_oid = '.1.3.6.1.4.1.41112.1.4.5.1.10.1'; //UBNT-AirMAX-MIB::ubntWlStatRxRate.1
        return array(
            new WirelessSensor('rate', $this->getDeviceId(), $tx_oid, 'airos-tx', 1, 'Tx Rate'),
            new WirelessSensor('rate', $this->getDeviceId(), $rx_oid, 'airos-rx', 1, 'Rx Rate'),
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
        $oid = '.1.3.6.1.4.1.41112.1.4.5.1.6.1'; //UBNT-AirMAX-MIB::ubntWlStatRssi.1
        $sensors = array(
            new WirelessSensor('rssi', $this->getDeviceId(), $oid, 'airos', 0, 'Overall RSSI')
        );

        $data = snmpwalk_cache_oid($this->getDevice(), 'ubntRadioRssi', array(), 'UBNT-AirMAX-MIB');
        foreach ($data as $index => $entry) {
            $sensors[] = new WirelessSensor(
                'rssi',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.41112.1.4.2.1.2.' . $index,
                'airos',
                $index,
                'RSSI: Chain ' . str_replace('1.', '', $index),
                $entry['ubntRadioRssi.1']
            );
        }

        return $sensors;
    }
}
