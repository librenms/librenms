<?php

/**
 * Dns.php
 *
 * Get version info about LibreNMS and various components/dependencies
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
 * @copyright  2021 Thomas Berberich
 * @author     Thomas Berberch <sourcehhdoctor@gmail.com>
 */

namespace LibreNMS\Util;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use LibreNMS\Enum\AddressFamily;
use LibreNMS\Interfaces\Geocoder;
use Net_DNS2_Exception;
use Net_DNS2_Lookups;
use Net_DNS2_Resolver;

class Dns implements Geocoder
{
    public function __construct(protected Net_DNS2_Resolver $resolver, protected SocketWrapper $socket)
    {
    }

    public function lookupIp(Device $device): ?string
    {
        if ($device->overwrite_ip) {
            return $device->overwrite_ip;
        }

        if (IP::isValid($device->hostname)) {
            return $device->hostname;
        }

        $result = match (LibrenmsConfig::get('dns.resolution_mode')) {
            'prefer_ipv6' => $this->resolveIP($device->hostname, AddressFamily::IPv6) ?? $this->resolveIP($device->hostname),
            'ipv6_only' => $this->resolveIP($device->hostname, AddressFamily::IPv6),
            'prefer_ipv4' => $this->resolveIP($device->hostname, AddressFamily::IPv4) ?? $this->resolveIP($device->hostname),
            'ipv4_only' => $this->resolveIP($device->hostname, AddressFamily::IPv4),
            default => $this->resolveIP($device->hostname),
        };

        if ($result === false) {
            // failed to resolve IP, if ip is set, determine if we should clear it
            return $this->lookupFailedShouldClearIpCache($device) ? null : $device->ip;
        }

        return $result;
    }

    /**
     * @param  string  $domain  Domain which has to be parsed
     * @param  string  $record  DNS Record which should be searched
     * @return array List of matching records
     */
    public function getRecord(string $domain, string $record = 'A'): array
    {
        try {
            $ret = $this->resolver->query($domain, $record);

            return $ret->answer;
        } catch (Net_DNS2_Exception $e) {
            d_echo('::query() failed: ' . $e->getMessage());

            return [];
        }
    }

    public function getCoordinates($hostname): array
    {
        $r = $this->getRecord((string) $hostname, 'LOC');

        foreach ($r as $record) {
            return [
                'lat' => $record->latitude ?? null,
                'lng' => $record->longitude ?? null,
            ];
        }

        return [];
    }

    /**
     * Resolve IPs for hostname IPv4 and IPv6. Respecting the order for dns.resolution_mode
     * @return string[]
     */
    public function resolveIPs(string $hostname, ?string $preferredIpFirst = null): array
    {
        $info = $this->socket->getAddrInfo($hostname);
        if ($info === false) {
            return [];
        }

        $addresses = array_column($info, 'ai_addr');
        $orig = array_filter(array_map(fn ($addr) => $addr['sin6_addr'] ?? $addr['sin_addr'] ?? null, $addresses));
        $ipv4 = array_filter(array_column($addresses, 'sin_addr'));
        $ipv6 = array_filter(array_column($addresses, 'sin6_addr'));

        // move preferred IP to the beginning of the list respecting resolution mode
        if ($preferredIpFirst !== null && ($orig_key = array_search($preferredIpFirst, $orig, true)) !== false) {
            $target_is_ipv4 = false;
            if (($key = array_search($preferredIpFirst, $ipv4, true)) !== false) {
                unset($ipv4[$key]);
                array_unshift($ipv4, $preferredIpFirst);
                $target_is_ipv4 = true;
            } elseif (($key = array_search($preferredIpFirst, $ipv6, true)) !== false) {
                unset($ipv6[$key]);
                array_unshift($ipv6, $preferredIpFirst);
            }

            // only put preferred IP in front of the same type of IPs
            foreach($orig as $k => $ip) {
                $is_ipv4 = IPv4::isValid($ip);
                if ($is_ipv4 === $target_is_ipv4 && $k < $orig_key) {
                    unset($orig[$orig_key]);
                    $orig = [...array_slice($orig, 0, $k), $preferredIpFirst, ...array_slice($orig, $k)];
                }
            }
        }

        $mode = LibrenmsConfig::get('dns.resolution_mode');

        return array_values(match ($mode) {
            'ipv4_only' => $ipv4,
            'ipv6_only' => $ipv6,
            'prefer_ipv4' => [...$ipv4, ...$ipv6],
            'prefer_ipv6' => [...$ipv6, ...$ipv4],
            default => $orig,
        });
    }

    public function resolveIP(string $hostname, ?AddressFamily $addressFamily = null): string|null|false
    {
        $info = $this->socket->getAddrInfo($hostname, $addressFamily);

        if ($info === false) {
            return false;
        }

        $ai_addr = $info[0]['ai_addr'] ?? [];

        return $ai_addr['sin6_addr'] ?? $ai_addr['sin_addr'] ?? null;
    }

    public function lookupFailedShouldClearIpCache(Device $device): bool
    {
        if ($device->ip === null) {
            return false; // if IP is already cleared, we don't need to check again
        }

        try {
            $types = match (LibrenmsConfig::get('dns.resolution_mode')) {
                'prefer_ipv4' => ['A', 'AAAA'],
                'ipv6_only' => ['AAAA'],
                'ipv4_only' => ['A'],
                'os' => in_array($device->transport, ['udp', 'tcp']) ? ['A', 'AAAA'] : ['AAAA', 'A'], // if the setting is default try to detect order
                default => ['AAAA', 'A'],
            };

            foreach ($types as $type) {
                $this->resolver->query($device->ip, $type);
            }
        } catch (\Net_DNS2_Exception $e) {
            if ($e->getCode() === Net_DNS2_Lookups::RCODE_NXDOMAIN) {
                return true;
            }

            // ignore other errors such as temp fail
        }

        return false;
    }
}
