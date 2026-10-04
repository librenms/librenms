<?php

/**
 * PortSpeed.php
 *
 * Helper for dealing with port speeds
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
 * @copyright  2026 Denny Friebe
 * @author     Denny Friebe <denny.friebe@icera-network.de>
 */

namespace LibreNMS\Util;

class PortSpeed
{
    /**
     * Parse symmetric or egress/ingress circuit speeds into bits per second.
     *
     * @return array{egress_speed?: int, ingress_speed?: int}
     */
    public static function parse(string $speed): array
    {
        $parts = explode('/', $speed, 2);
        $egress = Number::toBytes(trim($parts[0]));
        $ingress = isset($parts[1]) ? Number::toBytes(trim($parts[1])) : $egress;

        if (! is_finite($egress) || ! is_finite($ingress)
            || $egress < 1 || $ingress < 1
            || $egress >= PHP_INT_MAX || $ingress >= PHP_INT_MAX) {
            return [];
        }

        return ['egress_speed' => (int) $egress, 'ingress_speed' => (int) $ingress];
    }
}
