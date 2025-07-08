<?php

/**
 * Jetstream.php
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

namespace LibreNMS\OS;

use App\Facades\PortCache;
use App\Models\Ipv6Address;
use App\Models\PortVlan;
use App\Models\Route;
use App\Models\Vlan;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use LibreNMS\Exceptions\InvalidIpException;
use LibreNMS\Interfaces\Discovery\BasicVlanDiscovery;
use LibreNMS\Interfaces\Discovery\Ipv6AddressDiscovery;
use LibreNMS\Interfaces\Discovery\PortVlanDiscovery;
use LibreNMS\Interfaces\Discovery\RouteDiscovery;
use LibreNMS\OS;
use LibreNMS\Util\IPv6;
use SnmpQuery;

class Jetstream extends OS implements Ipv6AddressDiscovery, RouteDiscovery, BasicVlanDiscovery, PortVlanDiscovery
{
    public function discoverIpv6Addresses(): Collection
    {
        return \SnmpQuery::allowUnordered()->enumStrings()->walk('TPLINK-IPV6ADDR-MIB::ipv6ParaConfigAddrTable')
            ->mapTable(function ($data, $ipv6ParaConfigIfIndex, $ipv6ParaConfigAddrType, $ipv6ParaConfigSourceType, $ipv6ParaConfigAddress) {
                try {
                    $ip = IPv6::fromHexString($data['TPLINK-IPV6ADDR-MIB::ipv6ParaConfigAddress']);

                    // map to IP-MIB origin
                    $origin = match ($ipv6ParaConfigSourceType) {
                        'assignedIp' => 'manual',
                        'autoIp', 'assignedEUI64Ip', 'assignedLinklocalIp', 'negotiate' => 'linklayer',
                        'dhcpv6' => 'dhcp',
                        default => 'other',
                    };

                    return new Ipv6Address([
                        'ipv6_address' => $ip->uncompressed(),
                        'ipv6_compressed' => $ip->compressed(),
                        'ipv6_prefixlen' => $data['TPLINK-IPV6ADDR-MIB::ipv6ParaConfigPrefixLength'] ?? '',
                        'ipv6_origin' => $origin,
                        'port_id' => PortCache::getIdFromIfIndex($ipv6ParaConfigIfIndex, $this->getDevice()),
                        'context_name' => '',
                    ]);
                } catch (InvalidIpException $e) {
                    Log::error('Failed to parse IP: ' . $e->getMessage());

                    return null;
                }
            })->filter();
    }

    public function discoverRoutes(): Collection
    {
        $routes = new Collection;
        $routes = $routes->merge(\SnmpQuery::hideMib()->walk(['TPLINK-STATICROUTE-MIB::tpStaticRouteConfigTable'])
        ->mapTable(function ($data) {
            // iterface name "vlan[xxx]" where xxx=ifIndex
            if (preg_match('/^vlan([\d]+)$/i', $data['tpStaticRouteItemInterfaceName'], $intName)) { //other TP-LINKs
                $metric = $data['tpStaticRouteItemDistance'];
            } else {
                preg_match('/^vlan([\d]+)$/i', $data['tpStaticRouteItemDistance'], $intName); //T1600g-28TS wrong data, swapped distance/name fields
                $metric = $data['tpStaticRouteItemInterfaceName'];
            }

            if (! empty($intName)) {
                $ifIndex = $intName[1];

                return new Route([
                    'port_id' => PortCache::getIdFromIfIndex($ifIndex, $this->getDeviceId()) ?? 0,
                    'context_name' => '',
                    'inetCidrRouteIfIndex' => $ifIndex,
                    'inetCidrRouteType' => 3,
                    'inetCidrRouteProto' => 4,
                    'inetCidrRouteNextHopAS' => 0,
                    'inetCidrRouteMetric1' => $data['tpIPv6StaticRouteItemDistance'] ?? 0,
                    'inetCidrRouteDestType' => 'ipv4',
                    'inetCidrRouteDest' => $data['tpStaticRouteItemDesIp'] ?? '',
                    'inetCidrRouteNextHopType' => 'ipv4',
                    'inetCidrRouteNextHop' => $data['tpStaticRouteItemNextIp'] ?? '',
                    'inetCidrRoutePolicy' => 'zeroDotZero.' . $ifIndex,
                    'inetCidrRoutePfxLen' => $data['tpStaticRouteItemMask'] ?? '',
                ]);
            } else {
                return null;
            }
        }));

        $routes = $routes->merge(\SnmpQuery::hideMib()->walk(['TPLINK-IPV6STATICROUTE-MIB::tpIPv6StaticRouteConfigTable'])
        ->mapTable(function ($data) {
            // iterface name "vlan[xxx]" where xxx=ifIndex
            if (preg_match('/^vlan([\d]+)$/i', $data['tpIPv6StaticRouteItemInterfaceName'], $intName)) { //other TP-LINKs
                $metric = $data['tpIPv6StaticRouteItemDistance'];
            } else {
                preg_match('/^vlan([\d]+)$/i', $data['tpIPv6StaticRouteItemDistance'], $intName); //T1600g-28TS wrong data, swapped distance/name fields
                $metric = $data['tpIPv6StaticRouteItemInterfaceName'];
            }
            if (! empty($intName)) {
                $ifIndex = $intName[1];

                return new Route([
                    'port_id' => PortCache::getIdFromIfIndex($ifIndex, $this->getDeviceId()) ?? 0,
                    'context_name' => '',
                    'inetCidrRouteIfIndex' => $ifIndex,
                    'inetCidrRouteType' => 3,
                    'inetCidrRouteProto' => 4,
                    'inetCidrRouteNextHopAS' => 0,
                    'inetCidrRouteMetric1' => $metric,
                    'inetCidrRouteDestType' => 'ipv6',
                    'inetCidrRouteDest' => $data['tpIPv6StaticRouteItemDesIp'] ?? '',
                    'inetCidrRouteNextHopType' => 'ipv6',
                    'inetCidrRouteNextHop' => $data['tpIPv6StaticRouteItemNexthop'] ?? '',
                    'inetCidrRoutePolicy' => 'zeroDotZero.' . $ifIndex,
                    'inetCidrRoutePfxLen' => $data['tpIPv6StaticRouteItemPrefixLen'] ?? '',
                ]);
            } else {
                return null;
            }
        }));

        return $routes->filter();
    }

    public function discoverBasicVlanData(): Collection
    {
        return SnmpQuery::cache()->walk('TPLINK-DOT1Q-VLAN-MIB::vlanConfigTable')
            ->mapTable(function ($data, $vlan_id) {
                return new Vlan([
                    'vlan_vlan' => $vlan_id,
                    'vlan_domain' => 1,
                    'vlan_name' => $data['TPLINK-DOT1Q-VLAN-MIB::dot1qVlanDescription'],
                ]);
            });
    }

    public function discoverPortVlanData(Collection $vlans): Collection
    {
        $ret = new Collection;

        $vlanPortConfigTable = SnmpQuery::walk('TPLINK-DOT1Q-VLAN-MIB::vlanPortConfigTable')->table(1);
        foreach ($vlanPortConfigTable as $ifIndex => $data) {
            $portConfig[$data['TPLINK-DOT1Q-VLAN-MIB::vlanPortNumber']] = $ifIndex;
        }

        $vlanConfigTable = SnmpQuery::cache()->walk('TPLINK-DOT1Q-VLAN-MIB::vlanConfigTable')->table(1);
        foreach ($vlanConfigTable as $vlan_id => $data) {
            $types = ['TPLINK-DOT1Q-VLAN-MIB::vlanTagPortMemberAdd' => 0, 'TPLINK-DOT1Q-VLAN-MIB::vlanUntagPortMemberAdd' => 1];
            foreach ($types as $type => $tag) {
                $expand = $this->jetstreamExpand($data[$type] ?? []);
                foreach ($expand as $key => $port) {
                    $ifIndex = $portConfig[$port] ?? 0;
                    $ret->push(new PortVlan([
                        'vlan' => $vlan_id,
                        'baseport' => PortCache::bridgePortFromIfIndex($ifIndex),
                        'untagged' => $tag,
                        'port_id' => PortCache::getIdFromIfIndex($ifIndex, $this->getDeviceId()) ?? 0, // ifIndex from device
                    ]));
                }
            }
        }

        return $ret;
    }

    private function jetstreamExpand($var): array
    {
        $result = [];

        preg_match_all('#(LAG|\d+/\d+/)(\d+)(?:-(\d+))?#', $var, $lags);

        foreach ($lags[2] as $index => $start) {
            $end = $lags[3][$index] ?: $start;
            for ($i = $start; $i <= $end; $i++) {
                $result[] = $lags[1][$index] . $i; //need to be in form LAGx or 1/0/x
            }
        }

        return $result;
    }
}
