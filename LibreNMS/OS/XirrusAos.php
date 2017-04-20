<?php
/**
 * XirrusAos.php
 *
 * Xirrus AOS OS
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
use LibreNMS\Interfaces\Discovery\Sensors\WirelessNoiseFloorDiscovery;
use LibreNMS\OS;

class XirrusAos extends OS implements WirelessClientsDiscovery, WirelessNoiseFloorDiscovery
{

    /**
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessClients()
    {
        return array(
            new WirelessSensor(
                'clients',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.21013.1.2.12.1.2.22.0',
                'xirrus',
                0,
                'Clients'
            )
        );
    }

    /**
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessNoiseFloor()
    {
        $names = $this->getCacheByIndex('realtimeMonitorIfaceName', 'XIRRUS-MIB');
        $nf = snmp_cache_oid('realtimeMonitorNoiseFloor', $this->getDevice(), array(), 'XIRRUS-MIB');

        $sensors = array();
        foreach ($nf as $index => $entry) {
            $sensors[] = new WirelessSensor(
                'noise-floor',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.21013.1.2.24.7.1.10.' . $index,
                'xirrus',
                $index,
                $names[$index],
                $entry['realtimeMonitorNoiseFloor']
            );
        }

        return $sensors;
    }
}
