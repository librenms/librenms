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
 *
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\AccessPoint;
use App\Models\Device;
use Illuminate\Support\Collection;
use Illuminate\Support\Arr;
use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessApCountDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\Interfaces\Polling\WirelessAccessPointPolling;
use LibreNMS\Interfaces\Polling\OSPolling;
use LibreNMS\OS\Shared\Cisco;
use LibreNMS\RRD\RrdDefinition;

class Ciscowlc extends Cisco implements
    WirelessClientsDiscovery,
    WirelessApCountDiscovery,
    WirelessAccessPointPolling
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

    public function getWirelessControllerDatastorePrefix()
    {
        // Prefix used to name RRD-files for AccessPoints
        return 'cisco-controller';
    }

    public function getAccessPointDatastorePrefix()
    {
        // Prefix used to name RRD-files for AccessPoints
        return 'cisco-ap';
    }

    /**
     * Poll wireless access points data from the controller
     * Return collection of AccessPoints
     */
    public function pollWirelessAccessPoints()
    {
        $access_points = new Collection;
        $device = $this->getDeviceArray();
        $stats = snmpwalk_cache_oid($device, 'bsnAPEntry', $stats, 'AIRESPACE-WIRELESS-MIB', null, '-OQUsb');
        $radios = snmpwalk_cache_oid($device, 'bsnAPIfEntry', $radios, 'AIRESPACE-WIRELESS-MIB', null, '-OQUsb');
        $APstats = snmpwalk_cache_oid($device, 'bsnApIfNoOfUsers', $APstats, 'AIRESPACE-WIRELESS-MIB', null, '-OQUsxb');
        $loadParams = snmpwalk_cache_oid($device, 'bsnAPIfLoadChannelUtilization', $loadParams, 'AIRESPACE-WIRELESS-MIB', null, '-OQUsb');
        $interferences = snmpwalk_cache_oid($device, 'bsnAPIfInterferencePower', $interferences, 'AIRESPACE-WIRELESS-MIB', null, '-OQUsb');
   
        // Loop through the polled data.
        foreach ($radios as $key => $value) {
            $indexName = substr($key, 0, -2);
            $channel = str_replace('ch', '', $value['bsnAPIfPhyChannelNumber']);
            $mac = str_replace(' ', ':', $stats[$indexName]['bsnAPDot3MacAddress']);
            $name = $stats[$indexName]['bsnAPName'];
            $numasoclients = $value['bsnApIfNoOfUsers'];
            $radioArray = explode('.', $key);
            $radionum = array_pop($radioArray);
            $txpow = $value['bsnAPIfPhyTxPowerLevel'];
            $type = $value['bsnAPIfType'];
            $interference = 128 + $interferences[$key . '.' . $channel]['bsnAPIfInterferencePower'];
            $radioutil = $loadParams[$key]['bsnAPIfLoadChannelUtilization'];
        
            // TODO
            $numactbssid = 0;
            $nummonbssid = 0;
            $nummonclients = 0;
        
            d_echo("  name: $name\n");
            d_echo("  radionum: $radionum\n");
            d_echo("  type: $type\n");
            d_echo("  channel: $channel\n");
            d_echo("  txpow: $txpow\n");
            d_echo("  radioutil: $radioutil\n");
            d_echo("  numasoclients: $numasoclients\n");
            d_echo("  interference: $interference\n");
        
            // TODO: Is this really needed?
            // if there is a numeric channel, assume the rest of the data is valid, I guess
            if (! is_numeric($channel)) {
                continue;
            }

            $attributes = [
                'device_id' => $this->getDeviceId(),
                'name' => $name,
                'radio_number' => $radionum,
                'type' => $type,
                'mac_addr' => $mac,
                'channel' => $channel,
                'txpow' => $txpow / 2,
                'radioutil' => $radioutil,
                'numasoclients' => $numasoclients,
                'nummonclients' => $nummonclients,
                'numactbssid' => $numactbssid,
                'nummonbssid' => $nummonbssid,
                'interference' => $interference,
            ];

            // Create AccessPoint models
            $access_points->push(new AccessPoint($attributes));
        }

        // Return the collection of AccessPoint models
        return $access_points;
    }
}
