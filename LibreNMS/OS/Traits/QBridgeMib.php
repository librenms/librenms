<?php

/**
 * QBridgeMib.php
 *
 * -Description-
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
 * @copyright  2025 Peca Nesovanovic
 * @copyright  2025 Tony Murray
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS\Traits;

use App\Facades\PortCache;
use App\Models\PortVlan;
use App\Models\Vlan;
use Illuminate\Support\Collection;
use LibreNMS\Util\StringHelpers;
use SnmpQuery;

trait QBridgeMib
{
    private function discoverIetfQBridgeMibVlans(): Collection
    {
        return SnmpQuery::walk('Q-BRIDGE-MIB::dot1qVlanStaticName')
            ->mapTable(function ($data, $vlan_id) {
                return new Vlan([
                    'vlan_vlan' => $vlan_id,
                    'vlan_domain' => 1,
                    'vlan_name' => $data['Q-BRIDGE-MIB::dot1qVlanStaticName'] ?? '',
                ]);
            });
    }

    private function discoverIeeeQBridgeMibVlans(): Collection
    {
        return SnmpQuery::walk('IEEE8021-Q-BRIDGE-MIB::ieee8021QBridgeVlanStaticName')
            ->mapTable(function ($data, $vlan_domain_id, $vlan_id) {
                return new Vlan([
                    'vlan_vlan' => $vlan_id,
                    'vlan_domain' => $vlan_domain_id,
                    'vlan_name' => $data['IEEE8021-Q-BRIDGE-MIB::ieee8021QBridgeVlanStaticName'] ?? '',
                ]);
            });
    }

    private function discoverIetfQBridgeMibPorts(): Collection
    {
        $ports = new Collection;

        $vlanVersion = SnmpQuery::get('Q-BRIDGE-MIB::dot1qVlanVersionNumber.0')->value();

        if ($vlanVersion < 1 || $vlanVersion > 2) {
            return $ports;
        }

        // fetch vlan data
        $port_data = SnmpQuery::walk([
            'Q-BRIDGE-MIB::dot1qVlanCurrentUntaggedPorts',
            'Q-BRIDGE-MIB::dot1qVlanCurrentEgressPorts',
        ])->table(2);

        if (empty($port_data)) {
            // fall back to static
            $port_data = SnmpQuery::walk([
                'Q-BRIDGE-MIB::dot1qVlanStaticUntaggedPorts',
                'Q-BRIDGE-MIB::dot1qVlanStaticEgressPorts',
            ])->table(1);
        } else {
            // collapse timefilter from dot1qVlanCurrentTable results to only the newest
            $port_data = array_reduce($port_data, function ($result, $time_data) {
                foreach ($time_data as $vlan_id => $vlan_data) {
                    $result[$vlan_id] = isset($result[$vlan_id]) ? array_merge($result[$vlan_id], $vlan_data) : $vlan_data;
                }

                return $result;
            }, []);
        }

        foreach ($port_data as $vlan_id => $vlan) {
            //portmap for untagged ports
            $untagged = $vlan['Q-BRIDGE-MIB::dot1qVlanCurrentUntaggedPorts'] ?? $vlan['Q-BRIDGE-MIB::dot1qVlanStaticUntaggedPorts'] ?? '';
            $untagged_ids = StringHelpers::bitsToIndices($untagged);
            //portmap for members ports (might be tagged)
            $all = $vlan['Q-BRIDGE-MIB::dot1qVlanCurrentEgressPorts'] ?? $vlan['Q-BRIDGE-MIB::dot1qVlanStaticEgressPorts'] ?? '';
            $egress_ids = StringHelpers::bitsToIndices($all);

            foreach ($egress_ids as $baseport) {
                $ifIndex = $this->ifIndexFromBridgePort($baseport);
                if ($ifIndex === 0) {
                    // debug statements intentionally omitted due to possible high vlan/port counts
                    continue;
                }

                $port_id = PortCache::getIdFromIfIndex($ifIndex, $this->getDeviceId());
                if ($port_id === null) {
                    continue;
                }

                $ports->push(new PortVlan([
                    'vlan' => $vlan_id,
                    'baseport' => $baseport,
                    'untagged' => in_array($baseport, $untagged_ids) ? 1 : 0,
                    'port_id' => $port_id,
                ]));
            }
        }

        return $ports;
    }

    private function discoverIeeeQBridgeMibPorts(): Collection
    {
        $ports = new Collection;

        $port_data = SnmpQuery::walk([
            'IEEE8021-Q-BRIDGE-MIB::ieee8021QBridgeVlanStaticUntaggedPorts',
            'IEEE8021-Q-BRIDGE-MIB::ieee8021QBridgeVlanStaticEgressPorts',
        ])->table(2);

        if (empty($port_data)) {
            return $ports;
        }

        foreach ($port_data as $vlan_domain_id => $vlan_domains) {
            foreach ($vlan_domains as $vlan_id => $data) {
                //portmap for untagged ports
                $untagged_ids = StringHelpers::bitsToIndices($data['IEEE8021-Q-BRIDGE-MIB::ieee8021QBridgeVlanStaticUntaggedPorts'] ?? '');

                //portmap for members ports (might be tagged)
                $egress_ids = StringHelpers::bitsToIndices($data['IEEE8021-Q-BRIDGE-MIB::ieee8021QBridgeVlanStaticEgressPorts'] ?? '');

                foreach ($egress_ids as $baseport) {
                    $ports->push(new PortVlan([
                        'vlan' => $vlan_id,
                        'baseport' => $baseport,
                        'untagged' => (in_array($baseport, $untagged_ids) ? 1 : 0),
                        'port_id' => PortCache::getIdFromIfIndex($this->ifIndexFromBridgePort($baseport), $this->getDeviceId()) ?? 0, // ifIndex from device
                    ]));
                }
            }
        }

        return $ports;
    }
}
