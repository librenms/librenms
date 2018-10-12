<?php
/**
 * Routeros60g.php
 *
 * Mikrotik RouterOS
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
 * @copyright  2018 Evan Dent
 * @author     Evan Dent <evan@evandent.com>
 */

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRssiDiscovery;

use LibreNMS\OS;

class Routeros60g extends OS implements
    WirelessFrequencyDiscovery,
    WirelessRssiDiscovery
{
    private $data;

    /**
     * Discover wireless frequency.  This is in MHz. Type is frequency.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessFrequency()
    {
    $oid = '.1.3.6.1.4.1.14988.1.1.1.8.1.6.1';
    return array(
    	new WirelessSensor('frequency', $this->getDeviceId(), $oid, 'routeros60g', 1, 'Frequency')
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
	$oid = '.1.3.6.1.4.1.14988.1.1.1.8.1.12.1';
        return array(
            new WirelessSensor('rssi', $this->getDeviceId(), $oid, 'routeros60g', 1, 'Rssi')
	);

    }

}

