<?php

namespace LibreNMS\OS;

use App\Facades\PortCache;
use App\Models\Ipv6Address;
use App\Models\Route;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use LibreNMS\Exceptions\InvalidIpException;
use LibreNMS\Interfaces\Discovery\Ipv6AddressDiscovery;
use LibreNMS\Interfaces\Discovery\RouteDiscovery;
use LibreNMS\OS;
use LibreNMS\Util\IPv6;

class Jetstream extends OS implements Ipv6AddressDiscovery, RouteDiscovery
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
}
