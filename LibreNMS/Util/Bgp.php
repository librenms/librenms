<?php

/**
 * Bgp.php
 *
 * Decodes the values and indexes of BGP session tables.
 * BGP4-MIB cannot describe IPv6 sessions and BGP4-V2-MIB never left draft,
 * so vendors ship their own copy of one of them with the same value formats.
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
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Util;

class Bgp
{
    /** AS_TRANS, reported in place of a 4-byte AS by 2-byte only objects */
    public const AS_TRANS = 23456;

    /** InetAddressType to [index length, address length], the z types carry a 4 octet zone */
    private const ADDRESS_LENGTHS = [1 => [4, 4], 2 => [16, 16], 3 => [8, 4], 4 => [20, 16]];

    private const AFI = [1 => 'ipv4', 2 => 'ipv6', 25 => 'l2vpn'];

    private const SAFI = [
        1 => 'unicast',
        2 => 'multicast',
        3 => 'unicastAndMulticast',
        4 => 'labeledUnicast',
        5 => 'mvpn',
        65 => 'vpls',
        70 => 'evpn',
        128 => 'vpn',
        129 => 'vpnMulticast',
        132 => 'rtfilter',
        133 => 'flow',
    ];

    private const STATES = ['idle', 'connect', 'active', 'opensent', 'openconfirm', 'established'];

    public static function state(?string $state): string
    {
        $state = strtolower((string) $state);

        return in_array($state, self::STATES) ? $state : 'idle';
    }

    public static function afi(int|string $afi): string
    {
        return self::AFI[$afi] ?? (string) $afi;
    }

    public static function safi(int|string $safi): string
    {
        return self::SAFI[$safi] ?? (string) $safi;
    }

    public static function integer(int|string|null $value): ?int
    {
        return is_numeric($value) ? Number::correctIntegerOverflow((int) $value) : null;
    }

    /**
     * The database and rrd expect 32 bit counters. 64 bit counters may not fit in an int,
     * so take the remainder digit by digit.
     */
    public static function counter(int|string|null $value): ?int
    {
        if (! is_numeric($value)) {
            return null;
        }

        $value = (string) $value;
        if (! ctype_digit($value)) {
            return (Number::correctIntegerOverflow((int) $value) ?? 0) % 2 ** 32;
        }

        $low = 0;
        foreach (str_split($value) as $digit) {
            $low = ($low * 10 + (int) $digit) % 2 ** 32;
        }

        return $low;
    }

    /**
     * Split a two octet last error into its code and subcode, displayed as hex or as raw octets
     *
     * @return array{bgpPeerLastErrorCode?: int, bgpPeerLastErrorSubCode?: int}
     */
    public static function lastError(?string $value): array
    {
        $value = trim((string) $value, " \"\0");

        if (preg_match('/^([0-9a-f]{1,2})[ :]?([0-9a-f]{2})$/i', $value, $matches)) {
            return ['bgpPeerLastErrorCode' => (int) hexdec($matches[1]), 'bgpPeerLastErrorSubCode' => (int) hexdec($matches[2])];
        }

        if (strlen($value) == 2) {
            return ['bgpPeerLastErrorCode' => ord($value[0]), 'bgpPeerLastErrorSubCode' => ord($value[1])];
        }

        return [];
    }

    /**
     * The first usable AS, AS_TRANS only if nothing else is known
     *
     * @param  iterable<int|string|null>  $values
     */
    public static function localAs(iterable $values): ?int
    {
        $trans = null;

        foreach ($values as $value) {
            $as = self::integer($value);
            if ($as == self::AS_TRANS) {
                $trans = $as;
            } elseif ($as > 0) {
                return $as;
            }
        }

        return $trans;
    }

    /**
     * Take an InetAddressType and InetAddress off the front of an index.
     * Vendors disagree on whether the address is prefixed with its length, so both are accepted.
     *
     * @param  string[]  $parts  index parts, the address is removed
     * @param  int  $trailing  index parts after the address
     * @param  int|null  $zone  set to the zone (interface index) of scoped addresses
     */
    public static function shiftInetAddress(array &$parts, int $trailing = 0, ?int &$zone = null): ?IP
    {
        [$length, $addressLength] = self::ADDRESS_LENGTHS[(int) array_shift($parts)] ?? [null, null];
        $octets = $length === null ? null : self::shiftOctets($parts, $trailing, [$length]);
        if ($octets === null) {
            return null;
        }

        $zone = array_reduce(array_slice($octets, $addressLength), fn (int $zone, string $octet) => $zone * 256 + (int) $octet, 0) ?: null;

        return IP::fromSnmpString(implode('.', array_slice($octets, 0, $addressLength)), true);
    }

    /**
     * Take an address without a type off the front of an index, with or without a length
     *
     * @param  string[]  $parts  index parts, the address is removed
     * @param  int  $trailing  index parts after the address
     */
    public static function shiftIp(array &$parts, int $trailing = 0): ?IP
    {
        $octets = self::shiftOctets($parts, $trailing, [4, 16]);

        return $octets === null ? null : IP::fromSnmpString(implode('.', $octets), true);
    }

    /**
     * @param  string[]  $parts
     * @param  int[]  $lengths  valid index lengths
     * @return string[]|null
     */
    private static function shiftOctets(array &$parts, int $trailing, array $lengths): ?array
    {
        $available = count($parts) - $trailing;
        if (in_array($available - 1, $lengths) && $parts[0] == $available - 1) {
            array_shift($parts);
            $available--;
        } elseif (! in_array($available, $lengths)) {
            return null;
        }

        return array_splice($parts, 0, $available);
    }
}
