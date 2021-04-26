<?php
/**
 * Ciscowlc.php
 *
 * Cisco Wireless LAN Controller
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
use LibreNMS\Interfaces\Discovery\Sensors\WirelessApCountDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\OS\Shared\Cisco;

class Ciscowlc extends Cisco implements
    WirelessClientsDiscovery,
    WirelessApCountDiscovery
{
    /**
     * Discover wireless client counts. Type is clients.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessClients()
    {
        $ssids = $this->getCacheByIndex('bsnDot11EssSsid', 'AIRESPACE-WIRELESS-MIB');
        $counts = $this->getCacheByIndex('bsnDot11EssNumberOfMobileStations', 'AIRESPACE-WIRELESS-MIB');

        $sensors = [];
        $total_oids = [];
        $total = 0;
        foreach ($counts as $index => $count) {
            $oid = '.1.3.6.1.4.1.14179.2.1.1.1.38.' . $index;
            $total_oids[] = $oid;
            $total += $count;

            $sensors[] = new WirelessSensor(
                'clients',
                $this->getDeviceId(),
                $oid,
                'ciscowlc-ssid',
                $index,
                'SSID: ' . $ssids[$index],
                $count
            );
        }

        if (! empty($counts)) {
            $sensors[] = new WirelessSensor(
                'clients',
                $this->getDeviceId(),
                $total_oids,
                'ciscowlc',
                0,
                'Clients: Total',
                $total
            );
        }

        return $sensors;
    }

    /**
     * Discover wireless capacity.  This is a percent. Type is capacity.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessApCount()
    {
        $oids = [
            'CISCO-LWAPP-SYS-MIB::clsSysApConnectCount.0',
            'AIRESPACE-SWITCHING-MIB::agentInventoryMaxNumberOfAPsSupported.0',
        ];
        $data = snmp_get_multi($this->getDeviceArray(), $oids);

        if (isset($data[0]['clsSysApConnectCount'])) {
            return [
                new WirelessSensor(
                    'ap-count',
                    $this->getDeviceId(),
                    '.1.3.6.1.4.1.9.9.618.1.8.4.0',
                    'ciscowlc',
                    0,
                    'Connected APs',
                    $data[0]['clsSysApConnectCount'],
                    1,
                    1,
                    'sum',
                    null,
                    $data[0]['agentInventoryMaxNumberOfAPsSupported'],
                    0
                ),
            ];
        }

        return [];
    }
}
