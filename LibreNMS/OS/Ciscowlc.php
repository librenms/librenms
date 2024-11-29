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
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessApCountDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\Interfaces\Polling\OSPolling;
use LibreNMS\OS\Shared\Cisco;
use LibreNMS\RRD\RrdDefinition;
use SnmpQuery;

class Ciscowlc extends Cisco implements
    OSPolling,
    WirelessClientsDiscovery,
    WirelessApCountDiscovery
{
    public function pollOS(DataStorageInterface $datastore): void
    {
        if (! $this->getDevice()->wirelessSensors()->where('sensor_class', 'ap-count')->exists()) {
            return; // if ap count doesn't exist, skip this polling TODO replace with wireless controller module
        }

        $device = $this->getDeviceArray();
        $apNames = \SnmpQuery::enumStrings()->walk('AIRESPACE-WIRELESS-MIB::bsnAPName')->table(1);
        $radios = \SnmpQuery::enumStrings()->walk('AIRESPACE-WIRELESS-MIB::bsnAPIfTable')->table(2);
        \SnmpQuery::walk('AIRESPACE-WIRELESS-MIB::bsnAPIfLoadChannelUtilization')->table(2, $radios);
        $interferences = \SnmpQuery::walk('AIRESPACE-WIRELESS-MIB::bsnAPIfInterferencePower')->table(3);

        $numAccessPoints = count($apNames);
        $numClients = 0;

        foreach ($radios as $radio) {
            foreach ($radio as $slot) {
                $numClients += $slot['AIRESPACE-WIRELESS-MIB::bsnApIfNoOfUsers'] ?? 0;
            }
        }

        $rrd_def = RrdDefinition::make()
            ->addDataset('NUMAPS', 'GAUGE', 0, 12500000000)
            ->addDataset('NUMCLIENTS', 'GAUGE', 0, 12500000000);

        $fields = [
            'NUMAPS' => $numAccessPoints,
            'NUMCLIENTS' => $numClients,
        ];

        $tags = compact('rrd_def');
        $datastore->put($device, 'ciscowlc', $tags, $fields);

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
                if (! is_numeric($channel)) {
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

                $datastore->put($device, 'arubaap', [
                    'name' => $ap->name,
                    'radionum' => $ap->radio_number,
                    'rrd_name' => ['arubaap', $ap->name . $ap->radio_number],
                    'rrd_def' => $rrd_def,
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
        $counts = $this->getCacheByIndex('bsnDot11EssNumberOfMobileStations', 'AIRESPACE-WIRELESS-MIB');
        if (empty($counts)) {
            return []; // no counts to be had
        }

        $ssids = $this->getCacheByIndex('bsnDot11EssSsid', 'AIRESPACE-WIRELESS-MIB');
        if (empty($ssids)) {
            //  Try to check the LWAPP mib
            $ssids = $this->getCacheByIndex('cLWlanSsid', 'CISCO-LWAPP-WLAN-MIB');
        }

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

        $sensors[] = new WirelessSensor(
            'clients',
            $this->getDeviceId(),
            $total_oids,
            'ciscowlc',
            0,
            'Clients: Total',
            $total
        );

        return $sensors;
    }

    /**
     * Discover wireless capacity.  This is a percent. Type is capacity.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessApCount(): array
    {
        $data = SnmpQuery::get([
            'CISCO-LWAPP-SYS-MIB::clsSysApConnectCount.0',
            'AIRESPACE-SWITCHING-MIB::agentInventoryMaxNumberOfAPsSupported.0',
            'CISCO-LWAPP-AP-MIB::cLApGlobalAPConnectCount.0',
            'CISCO-LWAPP-AP-MIB::cLApGlobalMaxApsSupported.0',
        ])->values();

        if (isset($data['CISCO-LWAPP-AP-MIB::cLApGlobalAPConnectCount.0'])) {
            return [
                new WirelessSensor(
                    'ap-count',
                    $this->getDeviceId(),
                    '.1.3.6.1.4.1.9.9.513.1.3.35.0',
                    'ciscowlc',
                    0,
                    'Connected APs',
                    $data['CISCO-LWAPP-AP-MIB::cLApGlobalAPConnectCount.0'],
                    1,
                    1,
                    'sum',
                    null,
                    $data['CISCO-LWAPP-AP-MIB::cLApGlobalMaxApsSupported.0'],
                    0
                ),
            ];
        }

        if (isset($data['CISCO-LWAPP-SYS-MIB::clsSysApConnectCount.0'])) {
            return [
                new WirelessSensor(
                    'ap-count',
                    $this->getDeviceId(),
                    '.1.3.6.1.4.1.9.9.618.1.8.4.0',
                    'ciscowlc',
                    0,
                    'Connected APs',
                    $data['CISCO-LWAPP-SYS-MIB::clsSysApConnectCount.0'],
                    1,
                    1,
                    'sum',
                    null,
                    $data['AIRESPACE-SWITCHING-MIB::agentInventoryMaxNumberOfAPsSupported.0'],
                    0
                ),
            ];
        }

        return [];
    }
}
