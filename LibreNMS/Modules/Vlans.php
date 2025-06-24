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
use LibreNMS\Interfaces\Discovery\BasicVlanDiscovery;
use LibreNMS\Interfaces\Discovery\PortVlanDiscovery;
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
        $basic = new Collection;
        $ports = new Collection;

        // basic vlans data
        if ($os instanceof BasicVlanDiscovery) {
            $basic = $os->discoverBasicVlanData();
        }

        $basic = ($basic->isEmpty()) ? $this->discoverBasicVlanData($os->getDevice()) : $basic;
        $basic = ($basic->isEmpty()) ? $this->discoverBasicVlanData8021($os->getDevice()) : $basic;

        $basic = $basic->filter(function (Vlan $data) {
            if (empty($data['vlan_vlan'])) {
                return null;
            }

            return true;
        })->each(function (Vlan $data) {
            // default VLAN name
            $data['vlan_name'] = (empty($data['vlan_name'])) ? 'VLAN ' . $data['vlan_vlan'] : $data['vlan_name'];

            return $data;
        });

        Log::info(PHP_EOL . 'Basic Vlan data:');
        ModuleModelObserver::observe(\App\Models\Vlan::class);
        $this->syncModels($os->getDevice(), 'vlans', $basic);

        // ports vlan data
        if ($os instanceof PortVlanDiscovery) {
            $ports = $os->discoverPortVlanData();
        }

        $ports = ($ports->isEmpty()) ? $this->discoverPortVlanData($os->getDevice()) : $ports;
        $ports = ($ports->isEmpty()) ? $this->discoverPortVlanData8021($os->getDevice()) : $ports;

        $ports = $ports->filter(function (PortVlan $data) {
            if (empty($data['port_id']) || empty($data['vlan'])) {
                return null;
            }

            return true;
        })->each(function (PortVlan $data) {
            $data['priority'] = $data['priority'] ?? 0;
            $data['state'] = $data['state'] ?? 'unknown';
            $data['cost'] = $data['cost'] ?? 0;

            return $data;
        });
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

    private function discoverBasicVlanData(Device $device): Collection
    {
        $ret = new Collection;

        $oids = SnmpQuery::hideMib()->walk('Q-BRIDGE-MIB::dot1qVlanStaticName')->table(1);
        foreach ($oids as $vlan_id => $data) {
            $ret->push(new Vlan([
                'vlan_vlan' => $vlan_id,
                'vlan_domain' => 1,
                'vlan_name' => $data['dot1qVlanStaticName'] ?? '',
            ]));
        }

        return $ret;
    }

    private function discoverBasicVlanData8021(Device $device): Collection
    {
        $ret = new Collection;

        $oids = SnmpQuery::hideMib()->walk('IEEE8021-Q-BRIDGE-MIB::ieee8021QBridgeVlanStaticName')->table(2);
        foreach ($oids as $vlan_domain_id => $vlan_domains) {
            foreach ($vlan_domains as $vlan_id => $data) {
                $ret->push(new Vlan([
                    'vlan_vlan' => $vlan_id,
                    'vlan_domain' => $vlan_domain_id,
                    'vlan_name' => $data['ieee8021QBridgeVlanStaticName'] ?? '',
                ]));
            }
        }

        return $ret;
    }

    private function discoverPortVlanData(Device $device): Collection
    {
        $ret = new Collection;

        $vlanVersion = SnmpQuery::get('Q-BRIDGE-MIB::dot1qVlanVersionNumber.0')->value();

        if ($vlanVersion < 1 || $vlanVersion > 2) {
            return $ret;
        }

        $dot1dBasePortIfIndex = SnmpQuery::cache()->walk('BRIDGE-MIB::dot1dBasePortIfIndex')->table();
        $dot1dBasePortIfIndex = $dot1dBasePortIfIndex['BRIDGE-MIB::dot1dBasePortIfIndex'] ?? [];

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

        foreach ($oids as $vlan_id => $vlan) {
            //portmap for untagged ports
            $untagged_ids = q_bridge_bits2indices($vlan['dot1qVlanCurrentUntaggedPorts'] ?? $vlan['dot1qVlanStaticUntaggedPorts'] ?? '');
            //portmap for members ports (might be tagged)
            $egress_ids = q_bridge_bits2indices($vlan['dot1qVlanCurrentEgressPorts'] ?? $vlan['dot1qVlanStaticEgressPorts'] ?? '');

            foreach ($egress_ids as $baseport) {
                $ret->push(new PortVlan([
                    'vlan' => $vlan_id,
                    'baseport' => $baseport,
                    'untagged' => (in_array($baseport, $untagged_ids) ? 1 : 0),
                    'port_id' => PortCache::getIdFromIfIndex($dot1dBasePortIfIndex[$baseport] ?? 0, $device->device_id) ?? 0, // ifIndex from device
                ]));
            }
        }

        return $ret->filter();
    }

    private function discoverPortVlanData8021(Device $device): Collection
    {
        $ret = new Collection;

        $oids = SnmpQuery::hideMib()->walk('IEEE8021-Q-BRIDGE-MIB::ieee8021QBridgeVlanStaticUntaggedPorts')->table(2);
        $oids = SnmpQuery::hideMib()->walk('IEEE8021-Q-BRIDGE-MIB::ieee8021QBridgeVlanStaticEgressPorts')->table(2, $oids);

        if (empty($oids)) {
            return $ret;
        }

        $dot1dBasePortIfIndex = SnmpQuery::cache()->walk('BRIDGE-MIB::dot1dBasePortIfIndex')->table();
        $dot1dBasePortIfIndex = $dot1dBasePortIfIndex['BRIDGE-MIB::dot1dBasePortIfIndex'] ?? [];

        foreach ($oids as $vlan_domain_id => $vlan_domains) {
            foreach ($vlan_domains as $vlan_id => $data) {
                //portmap for untagged ports
                $untagged_ids = q_bridge_bits2indices($data['ieee8021QBridgeVlanStaticUntaggedPorts'] ?? '');

                //portmap for members ports (might be tagged)
                $egress_ids = q_bridge_bits2indices($data['ieee8021QBridgeVlanStaticEgressPorts'] ?? '');

                foreach ($egress_ids as $baseport) {
                    $ret->push(new PortVlan([
                        'vlan' => $vlan_id,
                        'baseport' => $baseport,
                        'untagged' => (in_array($baseport, $untagged_ids) ? 1 : 0),
                        'port_id' => PortCache::getIdFromIfIndex($dot1dBasePortIfIndex[$baseport] ?? 0, $device->device_id) ?? 0, // ifIndex from device
                    ]));
                }
            }
        }

        return $ret;
    }
}
