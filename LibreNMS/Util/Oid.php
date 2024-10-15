<?php
/*
 * Snmp.php
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2022 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Util;

use Cache;
use LibreNMS\Exceptions\InvalidOidException;

class Oid
{
    public static function isNumeric(string $oid): bool
    {
        return (bool) preg_match('/^[.\d]+$/', $oid);
    }

    public static function hasNumericRoot(string $oid): bool
    {
        return (bool) preg_match('/^\.?1/', $oid);
    }

    public static function hasNumeric(array $oids): bool
    {
        foreach ($oids as $oid) {
            if (self::isNumeric($oid)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Converts an oid to numeric and caches the result
     *
     * @throws \LibreNMS\Exceptions\InvalidOidException
     */
    public static function toNumeric(string $oid, string $mib = 'ALL', int $cache = 1800): string
    {
        if (Oid::isNumeric($oid)) {
            return $oid;
        }

        // we already have a specific mib, don't add a bunch of others
        if (str_contains($oid, '::')) {
            $mib = null;
        }

        $key = 'Oid:toNumeric:' . $oid . '/' . $mib;

        $numeric_oid = Cache::remember($key, $cache, function () use ($oid, $mib) {
            $snmpQuery = \SnmpQuery::numeric();

            if ($mib) {
                $snmpQuery->mibs([$mib], append: $mib !== 'ALL'); // append to base mibs unless using ALL
            }

            return $snmpQuery->translate($oid);
        });

        if (empty($numeric_oid)) {
            throw new InvalidOidException("Unable to translate oid $oid");
        }

        return $numeric_oid;
    }

    /**
     * Returns a numeric oid representation of a string.  The first octet is the string length.
     */
    public static function encodeString(string $string): string
    {
        return strlen($string) . '.' . implode('.', unpack('c*', $string));
    }

    /**
     * Try to parse an oid into a string.
     * If the oid contains multiple strings, skip until we get to the given position
     */
    public static function stringFromOid(string $oid, int $position = 0): string
    {
        $parts = explode('.', $oid);
        $count = count($parts);

        // walk through the parts
        $offset = 0;
        for ($i = 0; $i < $count; $i++) {
            $length = (int) $parts[$offset]; // get the string length
            $offset++; // skip over string length position

            if ($i == $position) {
                // convert the parts to a string
                return pack('c*', ...array_slice($parts, $offset, $length));
            }

            // skip processed parts
            $offset += $length;
        }

        return '';
    }
}
