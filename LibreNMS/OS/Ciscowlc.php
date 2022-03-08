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
use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessApCountDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\Interfaces\Polling\OSPolling;
use LibreNMS\OS\Shared\Cisco;
use LibreNMS\RRD\RrdDefinition;

class Ciscowlc extends Cisco implements
    OSPolling,
    WirelessClientsDiscovery,
    WirelessApCountDiscovery
{
    public function pollOS(): void
    {
        $device = $this->getDeviceArray();
        $apNames = \SnmpQuery::enumStrings()->walk('AIRESPACE-WIRELESS-MIB::bsnAPName')->table(1);
        $radios = \SnmpQuery::enumStrings()->walk('AIRESPACE-WIRELESS-MIB::bsnAPIfTable')->table(2);
        \SnmpQuery::walk('AIRESPACE-WIRELESS-MIB::bsnAPIfLoadChannelUtilization')->table(2, $radios);
        $interferences = \SnmpQuery::walk('AIRESPACE-WIRELESS-MIB::bsnAPIfInterferencePower')->table(3);

        $numAccessPoints = count($apNames);
        $numClients = 0;

        foreach ($radios as $radio) {
            foreach ($radio as $slot) {
                $numClients += $slot['AIRESPACE-WIRELESS-MIB::bsnApIfNoOfUsers'];
            }
        }

        $rrd_def = RrdDefinition::make()
            ->addDataset('NUMAPS', 'GAUGE', 0, 12500000000)
            ->addDataset('NUMCLIENTS', 'GAUGE', 0, 12500000000);

        $fields = [
            'NUMAPS'     => $numAccessPoints,
            'NUMCLIENTS' => $numClients,
        ];

        $tags = compact('rrd_def');
        data_update($device, 'ciscowlc', $tags, $fields);

        $db_aps = $this->getDevice()->accessPoints->keyBy->getCompositeKey();
        $valid_ap_ids = [];

        foreach ($radios as $mac => $radio) {
            foreach ($radio as $slot => $value) {
                $channel = str_replace('ch', '', $value['AIRESPACE-WIRELESS-MIB::bsnAPIfPhyChannelNumber'] ?? '');

                $ap = new AccessPoint([
                    'device_id' => $this->getDeviceId(),
                    'name' => $apNames[$mac]['AIRESPACE-WIRELESS-MIB::bsnAPName'] ?? '',
                    'radio_number' => $slot,
                    'type' => $value['AIRESPACE-WIRELESS-MIB::bsnAPIfType'] ?? '',
                    'mac_addr' => $mac,
                    'channel' => $channel,
                    'txpow' => $value['AIRESPACE-WIRELESS-MIB::bsnAPIfPhyTxPowerLevel'] ?? 0,
                    'radioutil' => $value['AIRESPACE-WIRELESS-MIB::bsnAPIfLoadChannelUtilization'] ?? 0,
                    'numasoclients' => $value['AIRESPACE-WIRELESS-MIB::bsnApIfNoOfUsers'] ?? 0,
                    'nummonclients' => 0,
                    'nummonbssid' => 0,
                    'interference' => 128 + ($interferences[$mac][$slot][$channel]['AIRESPACE-WIRELESS-MIB::bsnAPIfInterferencePower'] ?? -128), // why are we adding 128?
                ]);

                d_echo($ap->toArray());

                // if there is a numeric channel, assume the rest of the data is valid, I guess
                if (! is_numeric($ap->channel)) {
                    continue;
                }

                $rrd_def = RrdDefinition::make()
                    ->addDataset('channel', 'GAUGE', 0, 200)
                    ->addDataset('txpow', 'GAUGE', 0, 200)
                    ->addDataset('radioutil', 'GAUGE', 0, 100)
                    ->addDataset('nummonclients', 'GAUGE', 0, 500)
                    ->addDataset('nummonbssid', 'GAUGE', 0, 200)
                    ->addDataset('numasoclients', 'GAUGE', 0, 500)
                    ->addDataset('interference', 'GAUGE', 0, 2000);

                data_update($device, 'arubaap', [
                    'name' => $ap->name,
                    'radionum' => $ap->radio_number,
                    'rrd_name' => ['arubaap', $ap->name . $ap->radio_number],
                    'rrd_dev' => $rrd_def,
                ], $ap->only([
                    'channel',
                    'txpow',
                    'radioutil',
                    'nummonclients',
                    'nummonbssid',
                    'numasoclients',
                    'interference',
                ]));

                /** @var AccessPoint $db_ap */
                if ($db_ap = $db_aps->get($ap->getCompositeKey())) {
                    $ap = $db_ap->fill($ap->getAttributes());
                }

                $ap->save(); // persist ap
                $valid_ap_ids[] = $ap->accesspoint_id;
            }
        }

        // delete invalid aps
        $this->getDevice()->accessPoints->whereNotIn('accesspoint_id', $valid_ap_ids)->each->delete();
    }

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
