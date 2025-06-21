<?php

/**
 * Vlans.php
 *
 * Vlans discovery module
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
 * @link       http://librenms.org
 *
 * @copyright  2025 Peca Nesovanovic
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
 */

namespace LibreNMS\Modules;

use App\Facades\PortCache;
use App\Models\Device;
use App\Models\PortVlan;
use App\Models\Vlan;
use App\Observers\ModuleModelObserver;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Discovery\VlanDiscovery;
use LibreNMS\Interfaces\Module;
use LibreNMS\OS;
use LibreNMS\Polling\ModuleStatus;
use SnmpQuery;

class Vlans implements Module
{
    use SyncsModels;

    /**
     * @inheritDoc
     */
    public function dependencies(): array
    {
        return ['ports'];
    }

    /**
     * @inheritDoc
     */
    public function shouldDiscover(OS $os, ModuleStatus $status): bool
    {
        return $status->isEnabledAndDeviceUp($os->getDevice());
    }

    /**
     * @inheritDoc
     */
    public function shouldPoll(OS $os, ModuleStatus $status): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function discover(OS $os): void
    {
        $vlans = new Collection;
        $ports = new Collection;

        $dot1dBasePortIfIndex = SnmpQuery::hideMib()->walk('BRIDGE-MIB::dot1dBasePortIfIndex')->table();
        $dot1dBasePortIfIndex = $dot1dBasePortIfIndex['dot1dBasePortIfIndex'] ?? [];
        $index2base = array_flip($dot1dBasePortIfIndex);

        if ($os instanceof VlanDiscovery) {
            $vlanData = $os->discoverVlans($dot1dBasePortIfIndex);
        }

        if (empty($vlanData)) {
            $vlanData = $this->discoverVlans8021($os->getDevice(), $dot1dBasePortIfIndex);
        }

        if (empty($vlanData)) {
            $vlanData = $this->discoverVlans($os->getDevice(), $dot1dBasePortIfIndex);
        }

        foreach ($vlanData['basic'] ?? [] as $key => $data) { // basic vlan info
            //if vlan have no name, use generic 'VLAN x' where x is vlan_id
            $data['vlan_name'] = (empty($data['vlan_name'])) ? 'VLAN ' . $data['vlan_vlan'] : $data['vlan_name'];
            $vlans->push(new Vlan($data));
        }

        Log::info(PHP_EOL . 'Basic Vlan data:');
        ModuleModelObserver::observe(\App\Models\Vlan::class);
        $this->syncModels($os->getDevice(), 'vlans', $vlans);

        foreach ($vlanData['ports'] ?? [] as $key => $data) {
            $data['vlan'] = $data['vlan'] ?? 0;
            $data['baseport'] = $data['baseport'] ?? 0;
            $data['priority'] = $data['priority'] ?? 0;
            $data['state'] = $data['state'] ?? 'unknown';
            $data['cost'] = $data['cost'] ?? 0;
            $data['untagged'] = $data['untagged'] ?? 0;
            if (! empty($data['ifIndex'])) {
                $data['port_id'] = PortCache::getIdFromIfIndex($data['ifIndex'], $os->getDeviceId()); // ifIndex from device
            }
            if (empty($data['port_id'])) {
                $data['port_id'] = PortCache::getIdFromIfIndex($dot1dBasePortIfIndex[$data['baseport']] ?? 0, $os->getDeviceId()) ?? 0;
            }
            unset($data['ifIndex']);
            if (! empty($data['port_id']) && ! empty($data['vlan'])) { // sanity check
                $ports->push(new PortVlan($data));
            } else {
//              dump($data);
            }
        }

        Log::info(PHP_EOL . 'Ports Vlan data:');
        ModuleModelObserver::observe(\App\Models\PortVlan::class);
        $this->syncModels($os->getDevice(), 'portsVlan', $ports);
    }

    /**
     * @inheritDoc
     */
    public function poll(OS $os, DataStorageInterface $datastore): void
    {
        $this->discover($os);
    }

    /**
     * @inheritDoc
     */
    public function dataExists(Device $device): bool
    {
        return $device->vlans()->exists();
    }

    /**
     * @inheritDoc
     */
    public function cleanup(Device $device): int
    {
        return $device->vlans()->delete();
    }

    /**
     * @inheritDoc
     */
    public function dump(Device $device, string $type): ?array
    {
        return [
            'vlans' => $device->vlans()->orderBy('vlan_vlan')
                ->get()->map->makeHidden(['device_id', 'vlan_id']),
            'ports_vlans' => $device->portsVlan()
                ->orderBy('vlan')->orderBy('baseport')
                ->get()->map->makeHidden(['port_vlan_id', 'created_at', 'updated_at', 'device_id', 'port_id']),
        ];
    }

    private function discoverVlans(Device $device, $dot1dBasePortIfIndex): array
    {
        $vlanData = [];

        $vlanVersion = SnmpQuery::get('Q-BRIDGE-MIB::dot1qVlanVersionNumber.0')->value();

        if ($vlanVersion < 1 || $vlanVersion > 2) {
            return $vlanData;
        }

        // fetch vlan data
        $oids = SnmpQuery::hideMib()->walk('Q-BRIDGE-MIB::dot1qVlanCurrentUntaggedPorts')->table(2);
        $oids = SnmpQuery::hideMib()->walk('Q-BRIDGE-MIB::dot1qVlanCurrentEgressPorts')->table(2, $oids);
        if (empty($oids)) {
            // fall back to static
            $oids = SnmpQuery::hideMib()->walk('Q-BRIDGE-MIB::dot1qVlanStaticUntaggedPorts')->table(1, $oids);
            $oids = SnmpQuery::hideMib()->walk('Q-BRIDGE-MIB::dot1qVlanStaticEgressPorts')->table(1, $oids);
        } else {
            // collapse timefilter from dot1qVlanCurrentTable results to only the newest
            $oids = array_reduce($oids, function ($result, $time_data) {
                foreach ($time_data as $vlan_id => $vlan_data) {
                    $result[$vlan_id] = isset($result[$vlan_id]) ? array_merge($result[$vlan_id], $vlan_data) : $vlan_data;
                }

                return $result;
            }, []);
        }

        $oids = SnmpQuery::hideMib()->walk('Q-BRIDGE-MIB::dot1qVlanStaticName')->table(1, $oids);

        foreach ($oids as $vlan_id => $vlan) {
            $vlanData['basic'][] = [
                'vlan_vlan' => $vlan_id,
                'vlan_domain' => 1,
                'vlan_name' => $vlan['dot1qVlanStaticName'] ?? '',
            ];

            //portmap for untagged ports
            $untagged_ids = q_bridge_bits2indices($vlan['dot1qVlanCurrentUntaggedPorts'] ?? $vlan['dot1qVlanStaticUntaggedPorts'] ?? '');
            //portmap for members ports (might be tagged)
            $egress_ids = q_bridge_bits2indices($vlan['dot1qVlanCurrentEgressPorts'] ?? $vlan['dot1qVlanStaticEgressPorts'] ?? '');

            foreach ($egress_ids as $baseport) {
                $vlanData['ports'][] = [
                    'vlan' => $vlan_id,
                    'baseport' => $baseport,
                    'untagged' => (in_array($baseport, $untagged_ids) ? 1 : 0),
                    'ifIndex' => $dot1dBasePortIfIndex[$baseport] ?? 0,
                ];
            }
        }

        return $vlanData;
    }

    private function discoverVlans8021(Device $device, $dot1dBasePortIfIndex): array
    {
        $vlanData = [];

        $oids = SnmpQuery::hideMib()->walk('IEEE8021-Q-BRIDGE-MIB::ieee8021QBridgeVlanStaticUntaggedPorts')->table(2);
        $oids = SnmpQuery::hideMib()->walk('IEEE8021-Q-BRIDGE-MIB::ieee8021QBridgeVlanStaticEgressPorts')->table(2, $oids);
        $oids = SnmpQuery::hideMib()->walk('IEEE8021-Q-BRIDGE-MIB::ieee8021QBridgeVlanStaticName')->table(2, $oids);

        if (empty($oids)) {
            return $vlanData;
        }

        foreach ($oids as $vlan_domain_id => $vlan_domains) {
            foreach ($vlan_domains as $vlan_id => $data) {
                $vlanData['basic'][] = [
                    'vlan_vlan' => $vlan_id,
                    'vlan_domain' => $vlan_domain_id,
                    'vlan_name' => $data['ieee8021QBridgeVlanStaticName'] ?? '',
                ];

                //portmap for untagged ports
                $untagged_ids = q_bridge_bits2indices($data['ieee8021QBridgeVlanStaticUntaggedPorts'] ?? '');

                //portmap for members ports (might be tagged)
                $egress_ids = q_bridge_bits2indices($data['ieee8021QBridgeVlanStaticEgressPorts'] ?? '');

                foreach ($egress_ids as $baseport) {
                    $vlanData['ports'][] = [
                        'vlan' => $vlan_id,
                        'baseport' => $baseport,
                        'untagged' => (in_array($baseport, $untagged_ids) ? 1 : 0),
                        'ifIndex' => $dot1dBasePortIfIndex[$baseport] ?? 0,
                    ];
                }
            }
        }

        return $vlanData;
    }
}
