<?php
/**
 * BridgeMib.php
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
 * @copyright  2021 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS\Traits;

use App\Models\PortStp;
use App\Models\Stp;
use Illuminate\Support\Collection;
use LibreNMS\Util\Mac;
use SnmpQuery;

trait BridgeMib
{
    public function discoverStpInstances(?string $vlan = null): Collection
    {
        $protocol = SnmpQuery::get('BRIDGE-MIB::dot1dStpProtocolSpecification.0')->value();
        // 1 = unknown (mstp?), 3 = ieee8021d

        if ($protocol != 1 && $protocol != 3) {
            return new Collection;
        }

        $timeFactor = $this->stpTimeFactor ?? 0.01;

        // fetch STP config and store it
        $stp = SnmpQuery::context("$vlan", 'vlan-')->enumStrings()->get([
            'BRIDGE-MIB::dot1dBaseBridgeAddress.0',
            'BRIDGE-MIB::dot1dStpProtocolSpecification.0',
            'BRIDGE-MIB::dot1dStpPriority.0',
            'BRIDGE-MIB::dot1dStpTimeSinceTopologyChange.0',
            'BRIDGE-MIB::dot1dStpTopChanges.0',
            'BRIDGE-MIB::dot1dStpDesignatedRoot.0',
            'BRIDGE-MIB::dot1dStpRootCost.0',
            'BRIDGE-MIB::dot1dStpRootPort.0',
            'BRIDGE-MIB::dot1dStpMaxAge.0',
            'BRIDGE-MIB::dot1dStpHelloTime.0',
            'BRIDGE-MIB::dot1dStpHoldTime.0',
            'BRIDGE-MIB::dot1dStpForwardDelay.0',
            'BRIDGE-MIB::dot1dStpBridgeMaxAge.0',
            'BRIDGE-MIB::dot1dStpBridgeHelloTime.0',
            'BRIDGE-MIB::dot1dStpBridgeForwardDelay.0',
        ])->values();

        if (empty($stp)) {
            return new Collection;
        }

        $bridge = Mac::parseBridge($stp['BRIDGE-MIB::dot1dBaseBridgeAddress.0'] ?? '');
        $bridgeMac = $bridge->hex();
        $drBridge = Mac::parseBridge($stp['BRIDGE-MIB::dot1dStpDesignatedRoot.0'] ?? '');
        \Log::info(sprintf('VLAN: %s Bridge: %s DR: %s', $vlan ?: 1, $bridge->readable(), $drBridge->readable()));

        $instance = new \App\Models\Stp([
            'vlan' => $vlan,
            'rootBridge' => $bridgeMac == $drBridge->hex() ? 1 : 0,
            'bridgeAddress' => $bridgeMac,
            'protocolSpecification' => $stp['BRIDGE-MIB::dot1dStpProtocolSpecification.0'] ?? 'unknown',
            'priority' => $stp['BRIDGE-MIB::dot1dStpPriority.0'] ?? 0,
            'timeSinceTopologyChange' => substr($stp['BRIDGE-MIB::dot1dStpTimeSinceTopologyChange.0'] ?? '', 0, -2) ?: 0,
            'topChanges' => $stp['BRIDGE-MIB::dot1dStpTopChanges.0'] ?? 0,
            'designatedRoot' => $drBridge->hex(),
            'rootCost' => $stp['BRIDGE-MIB::dot1dStpRootCost.0'] ?? 0,
            'rootPort' => $stp['BRIDGE-MIB::dot1dStpRootPort.0'] ?? 0,
            'maxAge' => ($stp['BRIDGE-MIB::dot1dStpMaxAge.0'] ?? 0) * $timeFactor,
            'helloTime' => ($stp['BRIDGE-MIB::dot1dStpHelloTime.0'] ?? 0) * $timeFactor,
            'holdTime' => ($stp['BRIDGE-MIB::dot1dStpHoldTime.0'] ?? 0) * $timeFactor,
            'forwardDelay' => ($stp['BRIDGE-MIB::dot1dStpForwardDelay.0'] ?? 0) * $timeFactor,
            'bridgeMaxAge' => ($stp['BRIDGE-MIB::dot1dStpBridgeMaxAge.0'] ?? 0) * $timeFactor,
            'bridgeHelloTime' => ($stp['BRIDGE-MIB::dot1dStpBridgeHelloTime.0'] ?? 0) * $timeFactor,
            'bridgeForwardDelay' => ($stp['BRIDGE-MIB::dot1dStpBridgeForwardDelay.0'] ?? 0) * $timeFactor,
        ]);

        return (new Collection())->push($instance);
    }

    public function discoverStpPorts(Collection $stpInstances): Collection
    {
        $ports = new Collection;
        foreach ($stpInstances as $instance) {
            $vlan_ports = SnmpQuery::context("$instance->vlan", 'vlan-')
                ->enumStrings()->walk('BRIDGE-MIB::dot1dStpPortTable')
                ->mapTable(function ($data, $port) use ($instance) {
                    return new PortStp([
                        'vlan' => $instance->vlan,
                        'port_id' => $this->basePortToId($port),
                        'port_index' => $port,
                        'priority' => $data['BRIDGE-MIB::dot1dStpPortPriority'] ?? 0,
                        'state' => $data['BRIDGE-MIB::dot1dStpPortState'] ?? 'unknown',
                        'enable' => $data['BRIDGE-MIB::dot1dStpPortEnable'] ?? 'unknown',
                        'pathCost' => $data['BRIDGE-MIB::dot1dStpPortPathCost32'] ?? $data['BRIDGE-MIB::dot1dStpPortPathCost'] ?? 0,
                        'designatedRoot' => Mac::parseBridge($data['BRIDGE-MIB::dot1dStpPortDesignatedRoot'] ?? '')->hex(),
                        'designatedCost' => $data['BRIDGE-MIB::dot1dStpPortDesignatedCost'] ?? 0,
                        'designatedBridge' => Mac::parseBridge($data['BRIDGE-MIB::dot1dStpPortDesignatedBridge'] ?? '')->hex(),
                        'designatedPort' => $this->designatedPort($data['BRIDGE-MIB::dot1dStpPortDesignatedPort'] ?? ''),
                        'forwardTransitions' => $data['BRIDGE-MIB::dot1dStpPortForwardTransitions'] ?? 0,
                    ]);
                })->filter(function (PortStp $port) {
                    if ($port->enable === 'disabled') {
                        d_echo("$port->port_index ($port->vlan) disabled skipping\n");

                        return false;
                    }

                    if ($port->state === 'disabled') {
                        d_echo("$port->port_index ($port->vlan) state disabled skipping\n");

                        return false;
                    }

                    if (! $port->port_id) {
                        d_echo("$port->port_index ($port->vlan) port not found skipping\n");

                        return false;
                    }

                    d_echo("Discovered STP port $port->port_index ($port->vlan): $port->port_id");

                    return true;
                });

            $ports = $ports->merge($vlan_ports);
        }

        return $ports;
    }

    public function pollStpInstances(Collection $stpInstances): Collection
    {
        return $stpInstances->each(function (Stp $instance) {
            $data = SnmpQuery::context("$instance->vlan", 'vlan-')->enumStrings()->get([
                'BRIDGE-MIB::dot1dStpTimeSinceTopologyChange.0',
                'BRIDGE-MIB::dot1dStpTopChanges.0',
                'BRIDGE-MIB::dot1dStpDesignatedRoot.0',
            ])->values();

            $instance->timeSinceTopologyChange = substr($data['BRIDGE-MIB::dot1dStpTimeSinceTopologyChange.0'] ?? '', 0, -2) ?: 0;
            $instance->topChanges = $data['BRIDGE-MIB::dot1dStpTopChanges.0'] ?? 0;
            $instance->designatedRoot = Mac::parseBridge($data['BRIDGE-MIB::dot1dStpDesignatedRoot.0'] ?? '')->hex();
            $instance->rootBridge = $instance->bridgeAddress == $instance->designatedRoot; // dr might have changed
        });
    }

    public function pollStpPorts(Collection $stpPorts): Collection
    {
        foreach ($stpPorts->groupBy('vlan') as $vlan => $vlan_ports) {
            $vlan_ports = $vlan_ports->keyBy('port_index');
            $oids = $vlan_ports->keys()->sort()->reduce(function ($carry, $base_port) {
                $carry[] = 'BRIDGE-MIB::dot1dStpPortState.' . $base_port;
                $carry[] = 'BRIDGE-MIB::dot1dStpPortEnable.' . $base_port;
                $carry[] = 'BRIDGE-MIB::dot1dStpPortDesignatedRoot.' . $base_port;
                $carry[] = 'BRIDGE-MIB::dot1dStpPortDesignatedBridge.' . $base_port;

                return $carry;
            }, []);

            SnmpQuery::context("$vlan", 'vlan-')->enumStrings()->get($oids)
                ->mapTable(function ($data, $base_port) use ($vlan, $vlan_ports) {
                    $port = $vlan_ports->get($base_port);
                    $port->vlan = $vlan;
                    $port->state = $data['BRIDGE-MIB::dot1dStpPortState'] ?? 'unknown';
                    $port->enable = $data['BRIDGE-MIB::dot1dStpPortEnable'] ?? 'unknown';
                    $port->designatedRoot = Mac::parseBridge($data['BRIDGE-MIB::dot1dStpPortDesignatedRoot'] ?? '')->hex();
                    $port->designatedBridge = Mac::parseBridge($data['BRIDGE-MIB::dot1dStpPortDesignatedBridge'] ?? '')->hex();

                    return $port;
                });
        }

        return $stpPorts;
    }

    private function designatedPort(string $dp): int
    {
        if (preg_match('/-(\d+)/', $dp, $matches)) {
            // Syntax with "priority" dash "portID" like so : 32768-54, both in decimal
            return (int) $matches[1];
        }

        // Port saved in format priority+port (ieee 802.1d-1998: clause 8.5.5.1)
        $dp = substr($dp, -2); //discard the first octet (priority part)

        return (int) hexdec($dp);
    }
}
