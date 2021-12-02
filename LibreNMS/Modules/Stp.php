<?php
/*
 * Stp.php
 *
 * Spanning Tree
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
 * @copyright  2021 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Modules;

use App\Models\PortStp;
use App\Observers\ModuleModelObserver;
use Illuminate\Support\Collection;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Interfaces\Module;
use LibreNMS\OS;
use LibreNMS\Util\Rewrite;
use SnmpQuery;

class Stp implements Module
{
    use SyncsModels;

    public function discover(OS $os): void
    {
        $protocol = SnmpQuery::get('BRIDGE-MIB::dot1dStpProtocolSpecification.0')->value();
        // 1 = unknown (mstp?), 3 = ieee8021d

        if ($protocol != 1 && $protocol != 3) {
            return;
        }

        $timeFactor = $os->stpTimeFactor ?? 0.01;

        $vlans = $protocol == 1 ? $os->getDevice()->vlans : new Collection;
        $ports = new Collection;
        $instances = new Collection;

        foreach ($vlans->isEmpty() ? [null] : $vlans as $vlan) {
            // fetch STP config and store it
            $vlan = $vlan->vlan_vlan ?? null;
            $instance = SnmpQuery::context($vlan, 'vlan-')->enumStrings()->get([
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
            ]);

            if (! $instance->isValid()) {
                continue;
            }

            $stp = $instance->values();

            $bridge = Rewrite::macToHex($stp['BRIDGE-MIB::dot1dBaseBridgeAddress.0'] ?? '');
            $instances->push(new \App\Models\Stp([
                'vlan' => $vlan,
                'rootBridge' => $bridge == $this->rootToMac($stp['BRIDGE-MIB::dot1dStpDesignatedRoot.0'] ?? 0) ? 1 : 0,
                'bridgeAddress' => $bridge,
                'protocolSpecification' => $stp['BRIDGE-MIB::dot1dStpProtocolSpecification.0'] ?? 'unknown',
                'priority' => $stp['BRIDGE-MIB::dot1dStpPriority.0'] ?? 0,
                'timeSinceTopologyChange' => substr($stp['BRIDGE-MIB::dot1dStpTimeSinceTopologyChange.0'] ?? '', 0, -2) ?: 0,
                'topChanges' => $stp['BRIDGE-MIB::dot1dStpTopChanges.0'] ?? 0,
                'designatedRoot' => $this->rootToMac($stp['BRIDGE-MIB::dot1dStpDesignatedRoot.0'] ?? ''),
                'rootCost' => $stp['BRIDGE-MIB::dot1dStpRootCost.0'] ?? 0,
                'rootPort' => $stp['BRIDGE-MIB::dot1dStpRootPort.0'] ?? 0,
                'maxAge' => ($stp['BRIDGE-MIB::dot1dStpMaxAge.0'] ?? 0) * $timeFactor,
                'helloTime' => ($stp['BRIDGE-MIB::dot1dStpHelloTime.0'] ?? 0) * $timeFactor,
                'holdTime' => ($stp['BRIDGE-MIB::dot1dStpHoldTime.0'] ?? 0) * $timeFactor,
                'forwardDelay' => ($stp['BRIDGE-MIB::dot1dStpForwardDelay.0'] ?? 0) * $timeFactor,
                'bridgeMaxAge' => ($stp['BRIDGE-MIB::dot1dStpBridgeMaxAge.0'] ?? 0) * $timeFactor,
                'bridgeHelloTime' => ($stp['BRIDGE-MIB::dot1dStpBridgeHelloTime.0'] ?? 0) * $timeFactor,
                'bridgeForwardDelay' => ($stp['BRIDGE-MIB::dot1dStpBridgeForwardDelay.0'] ?? 0) * $timeFactor,
            ]));

            $ports = $ports->merge(SnmpQuery::context($vlan, 'vlan-')
                ->enumStrings()->walk('BRIDGE-MIB::dot1dStpPortTable')
                ->mapTable(function ($data, $port) use ($os, $vlan) {
                    return new PortStp([
                        'vlan' => $vlan,
                        'port_id' => $os->basePortToId($port),
                        'port_index' => $port,
                        'priority' => $data['BRIDGE-MIB::dot1dStpPortPriority'] ?? 0,
                        'state' => $data['BRIDGE-MIB::dot1dStpPortState'] ?? 'unknown',
                        'enable' => $data['BRIDGE-MIB::dot1dStpPortEnable'] ?? 'unknown',
                        'pathCost' => $data['BRIDGE-MIB::dot1dStpPortPathCost32'] ?? $data['BRIDGE-MIB::dot1dStpPortPathCost'] ?? 0,
                        'designatedRoot' => $this->rootToMac($data['BRIDGE-MIB::dot1dStpPortDesignatedRoot'] ?? ''),
                        'designatedCost' => $data['BRIDGE-MIB::dot1dStpPortDesignatedCost'] ?? 0,
                        'designatedBridge' => $this->rootToMac($data['BRIDGE-MIB::dot1dStpPortDesignatedBridge'] ?? ''),
                        'designatedPort' => $this->designatedPort($data['BRIDGE-MIB::dot1dStpPortDesignatedPort'] ?? ''),
                        'forwardTransitions' => $data['BRIDGE-MIB::dot1dStpPortForwardTransitions'] ?? 0,
                    ]);
                })->filter(function (PortStp $port) {
                    return $port->enable !== 'disabled' && $port->state !== 'disabled';
                }));
        }

        echo 'Instances: ';
        ModuleModelObserver::observe(\App\Models\Stp::class);
        $this->syncModels($os->getDevice(), 'stpInstances', $instances);
        echo "\nPorts: ";
//        foreach ($ports as $port) {
//            dump($port->toArray());
//        }
        ModuleModelObserver::observe(PortStp::class);
        $this->syncModels($os->getDevice(), 'stpPorts', $ports);
        echo PHP_EOL;
    }

    public function poll(OS $os): void
    {
        $ports = $os->getDevice()->stpPorts;

        if ($ports->isEmpty()) {
            return;
        }

        ModuleModelObserver::observe(PortStp::class);

        foreach ($ports->groupBy('vlan') as $vlan => $vlan_ports) {
            $vlan_ports = $vlan_ports->keyBy('port_index');
            $oids = $vlan_ports->keys()->sort()->reduce(function ($carry, $base_port) {
                $carry[] = 'BRIDGE-MIB::dot1dStpPortState.' . $base_port;
                $carry[] = 'BRIDGE-MIB::dot1dStpPortEnable.' . $base_port;
                $carry[] = 'BRIDGE-MIB::dot1dStpPortDesignatedRoot.' . $base_port;
                $carry[] = 'BRIDGE-MIB::dot1dStpPortDesignatedBridge.' . $base_port;

                return $carry;
            }, []);

            SnmpQuery::context($vlan, 'vlan-')->enumStrings()->get($oids)
                ->mapTable(function ($data, $base_port) use ($vlan, $vlan_ports) {
                    $port = $vlan_ports->get($base_port);
                    $port->vlan = $vlan;
                    $port->state = $data['BRIDGE-MIB::dot1dStpPortState'] ?? 'unknown';
                    $port->enable = $data['BRIDGE-MIB::dot1dStpPortEnable'] ?? 'unknown';
                    $port->designatedRoot = $this->rootToMac($data['BRIDGE-MIB::dot1dStpPortDesignatedRoot'] ?? '');
                    $port->designatedBridge = $this->rootToMac($data['BRIDGE-MIB::dot1dStpPortDesignatedBridge'] ?? '');

                    return $port;
                })->each->save();
        }
    }

    public function cleanup(OS $os): void
    {
        $os->getDevice()->stpInstances()->delete();
        $os->getDevice()->stpPorts()->delete();
    }

    /**
     * designated root is stored in format 2 octet bridge priority + MAC address, so we need to normalize it
     */
    public function rootToMac(string $root): string
    {
        $dr = str_replace(['.', ' ', ':', '-'], '', strtolower($root));

        return substr($dr, -12); //remove first two octets
    }

    public function designatedPort(string $dp): int
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
