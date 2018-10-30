<?php
/**
 * Ios.php
 *
 * Cisco IOS
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
use LibreNMS\Interfaces\Discovery\ProcessorDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRssiDiscovery;
use LibreNMS\OS;
use LibreNMS\OS\Shared\Cisco;

class Ios extends Cisco implements
    WirelessClientsDiscovery,
    WirelessRssiDiscovery
{
    /**
     * @return array Sensors
     */
    public function discoverWirelessClients()
    {
        $device = $this->getDevice();

        if (!starts_with($device['hardware'], 'AIR-') && !str_contains($device['hardware'], 'ciscoAIR')) {
            // unsupported IOS hardware
            return array();
        }

        $data = snmpwalk_cache_oid($device, 'cDot11ActiveWirelessClients', array(), 'CISCO-DOT11-ASSOCIATION-MIB');
        $entPhys = snmpwalk_cache_oid($device, 'entPhysicalDescr', array(), 'ENTITY-MIB');

        // fixup incorrect/missing entPhysicalIndex mapping
        foreach ($data as $index => $_unused) {
            foreach ($entPhys as $entIndex => $ent) {
                $descr = $ent['entPhysicalDescr'];
                unset($entPhys[$entIndex]); // only use each one once

                if (ends_with($descr, 'Radio')) {
                    d_echo("Mapping entPhysicalIndex $entIndex to ifIndex $index\n");
                    $data[$index]['entPhysicalIndex'] = $entIndex;
                    $data[$index]['entPhysicalDescr'] = $descr;
                    break;
                }
            }
        }

        $sensors = array();
        foreach ($data as $index => $entry) {
            $sensors[] = new WirelessSensor(
                'clients',
                $device['device_id'],
                ".1.3.6.1.4.1.9.9.273.1.1.2.1.1.$index",
                'ios',
                $index,
                $entry['entPhysicalDescr'],
                $entry['cDot11ActiveWirelessClients'],
                1,
                1,
                'sum',
                null,
                40,
                null,
                30,
                $entry['entPhysicalIndex'],
                'ports'
            );
        }
        return $sensors;
    }

    /**
     * Discover wireless RSSI (Received Signal Strength Indicator). This is in dBm. Type is rssi.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessRssi()
    {
        $sensors = array();

        $data = snmpwalk_cache_oid($this->getDevice(), 'c3gCurrentGsmRssi', array(), 'CISCO-WAN-3G-MIB');
        foreach ($data as $index => $entry) {
            $sensors[] = new WirelessSensor(
                'rssi',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.9.9.661.1.3.4.1.1.1.' . $index,
                'ios',
                $index,
                'RSSI: Chain ' . str_replace('1.', '', $index),
                $entry['c3gCurrentGsmRssi.1']
            );
        }

        return $sensors;
    }
}
