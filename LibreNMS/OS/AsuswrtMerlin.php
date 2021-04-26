<?php
/**
 * AsuswrtMerlin.php
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
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\Device;
use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessFrequencyDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessNoiseFloorDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRateDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSnrDiscovery;
use LibreNMS\OS;

class AsuswrtMerlin extends OS implements
    OSDiscovery,
    WirelessClientsDiscovery,
    WirelessFrequencyDiscovery,
    WirelessNoiseFloorDiscovery,
    WirelessRateDiscovery,
    WirelessSnrDiscovery
{
    public function discoverOS(Device $device): void
    {
        $info = explode(' ', snmp_get($this->getDeviceArray(), '.1.3.6.1.4.1.2021.7890.1.101.1', '-Osqnv'));
        $device->hardware = $info[1] ?? null;
        $device->version = $info[2] ?? null;
    }

    /**
     * Retrieve (and explode to array) list of network interfaces, and desired display name in LibreNMS.
     * This information is returned from the wireless device (router / AP) - as SNMP extend, with the name "interfaces".
     *
     * @return array Interfaces
     */
    private function getInterfaces()
    {
        // Need to use PHP_EOL, found newline (\n) not near as reliable / consistent! And this is as PHP says it should be done.
        $interfaces = explode(PHP_EOL, snmp_get($this->getDeviceArray(), 'NET-SNMP-EXTEND-MIB::nsExtendOutputFull."interfaces"', '-Osqnv'));
        $arrIfaces = [];
        foreach ($interfaces as $interface) {
            [$k, $v] = explode(',', $interface);
            $arrIfaces[$k] = $v;
        }

        return $arrIfaces;
    }

    /**
     * Generic (common / shared) routine, to create new Wireless Sensors, of the sensor Type passed as the call argument.
     * type - string, matching to LibreNMS documentation => https://docs.librenms.org/Developing/os/Wireless-Sensors/
     * query - string, query to be used at client (appends to type string, e.g. -tx, -rx)
     * system - boolean, flag to indicate that a combined ("system level") sensor (and OID) is to be added
     * stats - boolean, flag denoting that statistics are to be retrieved (min, max, avg)
     * NOTE: system and stats are assumed to be mutually exclusive (at least for now!)
     *
     * @return array Sensors
     */
    private function getSensorData($type, $query = '', $system = false, $stats = false)
    {
        // Initialize needed variables, and get interfaces (actual network name, and LibreNMS name)
        $sensors = [];
        $interfaces = $this->getInterfaces();
        $count = 1;

        // Build array for stats - if desired
        $statstr = [''];
        if ($stats) {
            $statstr = ['-min', '-max', '-avg'];
        }

        // Loop over interfaces, adding sensors
        foreach ($interfaces as $index => $interface) {
            // Loop over stats, appending to sensors as needed (only a single, blank, addition if no stats)
            foreach ($statstr as $stat) {
                $oid = "NET-SNMP-EXTEND-MIB::nsExtendOutput1Line.\"$type$query-$index$stat\"";
                $sensors[] = new WirelessSensor($type, $this->getDeviceId(), snmp_translate($oid), "openwrt$query", $count, "$interface$query$stat");
                $count += 1;
            }
        }
        // If system level (i.e. overall) sensor desired, add that one as well
        if ($system and (count($interfaces) > 1)) {
            $oid = "NET-SNMP-EXTEND-MIB::nsExtendOutput1Line.\"$type$query-wlan\"";
            $sensors[] = new WirelessSensor($type, $this->getDeviceId(), snmp_translate($oid), "openwrt$query", $count, 'wlan');
        }

        // And, return all the sensors that have been created above (i.e. the array of sensors)
        return $sensors;
    }

    /**
     * Discover wireless client counts. Type is clients.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessClients()
    {
        return $this->getSensorData('clients', '', true, false);
    }

    /**
     * Discover wireless frequency.  This is in MHz. Type is frequency.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessFrequency()
    {
        return $this->getSensorData('frequency', '', false, false);
    }

    /**
     * Discover wireless noise floor.  This is in dBm. Type is noise-floor.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessNoiseFloor()
    {
        return $this->getSensorData('noise-floor', '', false, false);
    }

    /**
     * Discover wireless rate. This is in bps. Type is rate.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessRate()
    {
        $txrate = $this->getSensorData('rate', '-tx', false, true);
        $rxrate = $this->getSensorData('rate', '-rx', false, true);

        return array_merge($txrate, $rxrate);
    }

    /**
     * Discover wireless snr. This is in dB. Type is snr.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessSNR()
    {
        return $this->getSensorData('snr', '', false, true);
    }
}
