<?php
/**
 * Deliberant.php
 *
 * LigoWave Deliberant
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

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\OS;

class Deliberant extends OS implements WirelessClientsDiscovery
{
    /**
     * Discover wireless client counts. Type is clients.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessClients()
    {
        $device = $this->getDeviceArray();
        $clients_data = snmpwalk_cache_oid($device, 'dlbDot11IfAssocNodeCount', [], 'DLB-802DOT11-EXT-MIB');

        $sensors = [];
        foreach ($clients_data as $index => $entry) {
            $sensors[] = new WirelessSensor(
                'clients',
                $device['device_id'],
                '.1.3.6.1.4.1.32761.3.5.1.2.1.1.16.' . $index,
                'deliberant',
                $index,
                'Clients'
            );
        }

        return $sensors;
    }
}
