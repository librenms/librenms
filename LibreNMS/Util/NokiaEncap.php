<?php

/**
 * NokiaEncap.php
 *
 * Decoding and formatting of TIMETRA-TC-MIB::TmnxEncapVal values.
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
 * @copyright  2026 Nick Peelman
 * @author     Nick Peelman <nick@peelman.us>
 */

namespace LibreNMS\Util;

class NokiaEncap
{
    /**
     * Decode TmnxEncapVal to extract VLAN ID(s)
     *
     * @param  int|string  $encapVal  The encoded encapsulation value
     * @return array Array with 'outer' and optionally 'inner' VLAN IDs
     *
     * @see TIMETRA-TC-MIB::TmnxEncapVal
     */
    public static function decode($encapVal): array
    {
        $encapVal = (int) $encapVal;

        // Null encapsulation
        if ($encapVal == 0) {
            return ['outer' => 0, 'inner' => null];
        }

        // Check for QinQ: if upper 16 bits have a value (ignoring special bits)
        $innerVlan = ($encapVal >> 16) & 0x0FFF;  // Upper 12 bits of upper 16 bits
        $outerVlan = $encapVal & 0x0FFF;          // Lower 12 bits

        if ($innerVlan > 0) {
            // QinQ encapsulation
            return ['outer' => $outerVlan, 'inner' => $innerVlan];
        }

        // Simple dot1q encapsulation - VLAN is in lower 12 bits
        return ['outer' => $outerVlan, 'inner' => null];
    }

    /**
     * Format TmnxEncapVal for display (Nokia-friendly format)
     *
     * @param  int|string  $encapVal  The encoded encapsulation value
     * @return string Formatted encap value (e.g., "500" or "100.200" for QinQ)
     */
    public static function format($encapVal): string
    {
        $decoded = self::decode($encapVal);

        if ($decoded['inner'] !== null) {
            // QinQ format: outer.inner
            return $decoded['outer'] . '.' . $decoded['inner'];
        }

        if ($decoded['outer'] == 4095) {
            return '*';  // Wildcard
        }

        return (string) $decoded['outer'];
    }
}
