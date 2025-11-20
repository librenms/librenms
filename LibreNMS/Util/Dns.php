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
    public function __construct(protected Net_DNS2_Resolver $resolver, protected Socket $socket)
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
