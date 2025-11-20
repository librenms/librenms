<?php

/**
 * Socket.php
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
 * @copyright  2025 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Util;

use LibreNMS\Enum\AddressFamily;

class Socket
{
    private array $cache = [];

    /**
     * @return array<array{
     *      ai_flags: int,
     *      ai_family: int,
     *      ai_socktype: int,
     *      ai_protocol: int,
     *      ai_addr: array{sin_port: int, sin_addr: string}|array{sin6_port: int, sin6_addr: string}
     *  }>|false
     */
    public function getAddrInfo(string $hostname, ?AddressFamily $family = null): array|false
    {
        $cacheKey = $hostname . ($family ? ":{$family->value}" : '');

        if (! array_key_exists($cacheKey, $this->cache)) {
            $hints = match ($family) {
                AddressFamily::IPv4 => ['ai_family' => AF_INET],
                AddressFamily::IPv6 => ['ai_family' => AF_INET6],
                default => [],
            };

            $info = socket_addrinfo_lookup($hostname, null, $hints);
            $this->cache[$cacheKey] = $info === false ? false : array_map(socket_addrinfo_explain(...), $info);
        }

        return $this->cache[$cacheKey];
    }
}
