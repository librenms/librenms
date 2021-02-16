<?php
/**
 * Radwin.php
 *
 * Radwin
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2018 Vivia Nguyen-Tran
 * @author     Vivia Nguyen-Tran<nguyen_vivia@hotmail.com>
 */

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessDistanceDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessPowerDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRssiDiscovery;
use LibreNMS\OS;

class Radwin extends OS implements
    WirelessDistanceDiscovery,
    WirelessPowerDiscovery,
    WirelessRssiDiscovery
{
    /**
     * Discover wireless distance.  This is in Kilometers. Type is distance.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessDistance()
    {
        $oid = '.1.3.6.1.4.1.4458.1000.1.5.29.0'; //RADWIN-MIB-WINLINK1000::winlink1000OduAirLinkDistance.0

        return [
            new WirelessSensor('distance', $this->getDeviceId(), $oid, 'radwin', 0, 'Link distance', null, 1, 1000),
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
        $transmit = '.1.3.6.1.4.1.4458.1000.1.5.4.0'; //RADWIN-MIB-WINLINK1000::winlink1000OduAirTxPower.0
        $receive = '.1.3.6.1.4.1.4458.1000.1.5.9.1.0'; //RADWIN-MIB-WINLINK1000::winlink1000OduAirRxPower.0

        return [
            new WirelessSensor('power', $this->getDeviceId(), $transmit, 'Radwin-Tx', 0, 'Tx Power'),
            new WirelessSensor('power', $this->getDeviceId(), $receive, 'Radwin-Rx', 0, 'Rx Power'),
        ];
    }

    /**
     * Discover wireless RSSI (Received Signal Strength Indicator). This is in dBm. Type is rssi.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessRssi()
    {
        $oid = '.1.3.6.1.4.1.4458.1000.1.1.51.7.0'; // RADWIN-MIB-WINLINK1000::winlink1000OduAdmWifiRssi.0

        return [
            new WirelessSensor('rssi', $this->getDeviceId(), $oid, 'radwin', 0, 'RSSI'),
        ];
    }
}
