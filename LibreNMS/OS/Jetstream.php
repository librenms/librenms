<?php

namespace LibreNMS\OS;

use App\Facades\PortCache;
use App\Models\Ipv6Address;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use LibreNMS\Exceptions\InvalidIpException;
use LibreNMS\Interfaces\Discovery\Ipv6AddressDiscovery;
use LibreNMS\OS;
use LibreNMS\Util\IPv6;

class Jetstream extends OS implements Ipv6AddressDiscovery
{
    public function discoverIpv6Addresses(): Collection
    {
        return \SnmpQuery::enumStrings()->walk('TPLINK-IPV6ADDR-MIB::ipv6ParaConfigAddrTable')
            ->mapTable(function ($data, $ipv6ParaConfigIfIndex, $ipv6ParaConfigAddrType, $ipv6ParaConfigSourceType, $ipv6ParaConfigAddress) {
                try {
                    $ip = IPv6::fromHexString($data['TPLINK-IPV6ADDR-MIB::ipv6ParaConfigAddress']);

                    // map to IP-MIB origin
                    $origin = match($ipv6ParaConfigSourceType) {
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
                        'port_id' => PortCache::getIdFromIfIndex($ipv6ParaConfigIfIndex, $this->getDevice()) ?? 0,
                    ]);
                } catch (InvalidIpException $e) {
                    Log::error('Failed to parse IP: ' . $e->getMessage());
                    return null;
                }
            })->filter();
        }
}
