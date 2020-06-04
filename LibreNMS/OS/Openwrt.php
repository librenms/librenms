<?php
/**
 * Openwrt.php
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
use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\OS;

class Openwrt extends OS implements
    WirelessClientsDiscovery,
    WirelessFrequencyDiscovery
{
    /**
     * Retrieve (and explode to array) list of network interfaces, and desired display name in LibreNMS.
     * This information is returned from the wireless device (router / AP) - as SNMP extend, with the name "interfaces".
     *
     * @return array Interfaces
     */
    private function getInterfaces()
    {
        // Need to use PHP_EOL, found newline (\n) not near as reliable / consistent! And this is as PHP says it should be done.
        $interfaces = explode(PHP_EOL, snmp_get($this->getDevice(), 'NET-SNMP-EXTEND-MIB::nsExtendOutputFull."interfaces"', '-Osqnv'));
        $arrIfaces = array();
        foreach ($interfaces as $interface) {
                list($k, $v) = explode(',', $interface);
                $arrIfaces[$k] = $v;
        }
        return $arrIfaces;
    }

    /**
     * Generic (common / shared) routine, to create new Wireless Sensors, of the sensor Type passed as the call argument.
     * type - string, matching to LibreNMS documentation => https://docs.librenms.org/Developing/os/Wireless-Sensors/
     *
     * @return array Sensors
     */
    private function getSensorData($type, $system = False)
    {
        $sensors = array();
        $interfaces = $this->getInterfaces();
        $count = 1;
        foreach ($interfaces as $index => $interface) {
                $oid = "NET-SNMP-EXTEND-MIB::nsExtendOutput1Line.\"$type-$index\"";
                $sensors[] = new WirelessSensor($type, $this->getDeviceId(), snmp_translate($oid), 'openwrt', $count, $interface);
                $count += 1;
        }
        if ($system and (count($interfaces) > 1)) {
                $oid = "NET-SNMP-EXTEND-MIB::nsExtendOutput1Line.\"$type-wlan\"";
                $sensors[] = new WirelessSensor($type, $this->getDeviceId(), snmp_translate($oid), 'openwrt', $count, 'wlan');
        }
        return $sensors;
    }

    /**
     * Discover wireless frequency.  This is in MHz. Type is frequency.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessFrequency()
    {
        return $this->getSensorData('frequency');
    }

    /**
     * Discover wireless client counts. Type is clients.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessClients()
    {
	return $this->getSensorData('clients', True);
    }

}
