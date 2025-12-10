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

use App\Models\Device;
use LibreNMS\Enum\AddressFamily;
use LibreNMS\Interfaces\Geocoder;
use Net_DNS2_Resolver;

class Dns implements Geocoder
{
    public function __construct(protected Net_DNS2_Resolver $resolver)
    {
    }

    public static function lookupIp(Device $device): ?string
    {
        if ($device->overwrite_ip) {
            return $device->overwrite_ip;
        }

        if (IP::isValid($device->hostname)) {
            return $device->hostname;
        }

        $addresses = app(self::class)->getAddresses($device->hostname);

        if ($device->transport == 'udp6' || $device->transport == 'tcp6') {
            foreach ($addresses as $address) {
                if (IPv6::isValid($address)) {
                    return $address;
                }
            }
        }

        return array_first($addresses);
    }

    /**
     * Get all IPs for a hostname
     *
     * @return string[]
     */
    public function getAddresses(string $hostname, ?AddressFamily $family = null): array
    {
        $hints = match ($family) {
            AddressFamily::IPv4 => ['ai_family' => AF_INET, 'ai_socktype' => SOCK_RAW],
            AddressFamily::IPv6 => ['ai_family' => AF_INET6, 'ai_socktype' => SOCK_RAW],
            default => ['ai_socktype' => SOCK_RAW],
        };

        $result = socket_addrinfo_lookup($hostname, null, $hints);

        if ($result === false) {
            return [];
        }

        return array_map(function ($info) {
            $explaned = socket_addrinfo_explain($info);

            return $explaned['ai_addr']['sin6_addr'] ?? $explaned['ai_addr']['sin_addr'];
        }, $result);
    }

    /**
     * @param  string  $domain  Domain which has to be parsed
     * @param  string  $record  DNS Record which should be searched
     * @return array<\Net_DNS2_RR> List of matching records
     */
    public function getRecord(string $domain, string $record = 'A'): array
    {
        try {
            $ret = $this->resolver->query($domain, $record);

            return $ret->answer;
        } catch (\Net_DNS2_Exception $e) {
            d_echo('::query() failed: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * @param  string  $hostname
     * @return array
     */
    public function getCoordinates($hostname): array
    {
        $r = $this->getRecord($hostname, 'LOC');

        foreach ($r as $record) {
            return [
                'lat' => $record->latitude ?? null,
                'lng' => $record->longitude ?? null,
            ];
        }

        return [];
    }
}
