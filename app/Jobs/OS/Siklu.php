<?php
/**
 * Siklu.php
 *
 * Siklu Communication
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
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRssiDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSnrDiscovery;
use LibreNMS\OS;

class Siklu extends OS implements
    WirelessFrequencyDiscovery,
    WirelessPowerDiscovery,
    WirelessRssiDiscovery,
    WirelessSnrDiscovery
{

    /**
     * Discover wireless frequency.  This is in GHz. Type is frequency.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessFrequency()
    {
        $oid = '.1.3.6.1.4.1.31926.2.1.1.4.1'; // RADIO-BRIDGE-MIB::rfOperationalFrequency.1
        return array(
            new WirelessSensor('frequency', $this->getDeviceId(), $oid, 'siklu', 1, 'Frequency', null, 1, 1000),
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
        $oid = '.1.3.6.1.4.1.31926.2.1.1.42.1'; // RADIO-BRIDGE-MIB::rfTxPower.1
        return array(
            new WirelessSensor('power', $this->getDeviceId(), $oid, 'siklu', 1, 'Tx Power'),
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
        $oid = '.1.3.6.1.4.1.31926.2.1.1.19.1'; // RADIO-BRIDGE-MIB::rfAverageRssi.1
        return array(
            new WirelessSensor('rssi', $this->getDeviceId(), $oid, 'siklu', 1, 'RSSI'),
        );
    }

    /**
     * Discover wireless SNR.  This is in dB. Type is snr.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessSnr()
    {
        $oid = '.1.3.6.1.4.1.31926.2.1.1.18.1'; // RADIO-BRIDGE-MIB::rfAverageCinr.1
        return array(
            new WirelessSensor('snr', $this->getDeviceId(), $oid, 'siklu', 1, 'CINR'),
        );
    }
}
