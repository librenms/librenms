<?php
/**
 * Ceraos.php
 *
 * Ceragon CeraOS
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
 * @copyright  2018 Maikel de Boer and Janno Schouwenburg
 * @author     Maikel de Boer <mdb@tampnet.com>, Janno Schouwenburg <js@tampnet.com>
 */

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessPowerDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRssiDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSnrDiscovery;
use LibreNMS\OS;

class Aprisa extends OS implements 
    WirelessPowerDiscovery, 
    WirelessRssiDiscovery,
    WirelessSnrDiscovery
{
    /**
     * Discover wireless tx power. This is in dBm. Type is power.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessPower()
    {
        $oid = '.1.3.6.1.4.1.14817.7.3.1.2.36.8.0';
        return array(
            new WirelessSensor('power', $this->getDeviceId(), $oid, 'aprisaostx', 1, 'TX Power')
        );
    }

    /**
     * Discover wireless rx rssi. This is in dBm. Type is power.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessRssi()
    {
        $oid = '.1.3.6.1.4.1.14817.7.3.1.2.51.6.0';
        return array(
            new WirelessSensor('rssi', $this->getDeviceId(), $oid, 'aprisaosrx', 1, 'RX Power', null, 1, 10),
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
        $oid = '.1.3.6.1.4.1.14817.7.3.1.2.6.3.0';
        return array(
            new WirelessSensor('snr', $this->getDeviceId(), $oid, 'aprisaossnr', 1, 'SNR', null, 1, 100),
        );
    }
}
