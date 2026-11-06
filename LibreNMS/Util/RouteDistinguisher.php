<?php

/**
 * RouteDistinguisher.php
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

class RouteDistinguisher
{
    /**
     * Devices report route distinguishers as text, as text with a trailing null,
     * or as the 8 octet RFC 4364 value. Returns null when it is not set.
     */
    public static function parse(?string $value): ?string
    {
        $value = trim((string) $value, ' "');
        $octets = preg_match('/^[0-9a-f]{2}( [0-9a-f]{2})+$/i', $value) ? (string) hex2bin(str_replace(' ', '', $value)) : $value;

        $text = trim($octets, " \0");
        if ($text !== '' && ctype_print($text)) {
            return str_contains($text, ':') ? $text : null; // some devices report INVALID or similar
        }

        return strlen($octets) == 8 ? self::fromOctets($octets) : null;
    }

    /**
     * A 2 octet type, then administrator and assigned number subfields (RFC 4364 4.2)
     */
    private static function fromOctets(string $octets): ?string
    {
        if (trim($octets, "\0") === '') {
            return null;
        }

        $type = unpack('n', substr($octets, 0, 2))[1];
        $value = substr($octets, 2);

        if ($type == 0) {
            $parts = unpack('nadmin/Nnumber', $value);

            return "{$parts['admin']}:{$parts['number']}";
        }

        if ($type == 1) {
            return inet_ntop(substr($value, 0, 4)) . ':' . unpack('n', substr($value, 4))[1];
        }

        if ($type == 2) {
            $parts = unpack('Nadmin/nnumber', $value);

            return "{$parts['admin']}:{$parts['number']}";
        }

        return null;
    }
}
