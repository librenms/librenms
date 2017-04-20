<?php
/**
 * Routeros.php
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
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessCcqDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessNoiseFloorDiscovery;
use LibreNMS\OS;

class Routeros extends OS implements WirelessClientsDiscovery, WirelessNoiseFloorDiscovery, WirelessCcqDiscovery
{
    private $data;

    /**
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessClients()
    {
        return $this->discoverSensor(
            'clients',
            'mtxrWlApClientCount',
            '.1.3.6.1.4.1.14988.1.1.1.3.1.6.'
        );
    }

    /**
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessCcq()
    {
        return $this->discoverSensor(
            'ccq',
            'mtxrWlApOverallTxCCQ',
            '.1.3.6.1.4.1.14988.1.1.1.3.1.10.'
        );
    }

    /**
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessNoiseFloor()
    {
        return $this->discoverSensor(
            'noise-floor',
            'mtxrWlApNoiseFloor',
            '.1.3.6.1.4.1.14988.1.1.1.3.1.9.'
        );
    }


    private function fetchData()
    {
        if (is_null($this->data)) {
            $this->data = snmpwalk_cache_oid($this->getDevice(), 'mtxrWlApTable', array(), 'MIKROTIK-MIB');
        }

        return $this->data;
    }

    private function discoverSensor($type, $oid, $num_oid_base)
    {
        $data = $this->fetchData();

        $sensors = array();
        foreach ($data as $index => $entry) {
            $sensors[] = new WirelessSensor(
                $type,
                $this->getDeviceId(),
                $num_oid_base . $index,
                'mikrotik',
                $index,
                'SSID: ' . $entry['mtxrWlApSsid'],
                $entry[$oid]
            );
        }

        return $sensors;
    }
}
