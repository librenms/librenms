<?php

/**
 * XklTrapUtil.php
 *
 * -Description-
 *
 * Utility class for handling XKL traps
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
 * @copyright  2026 Heath Barnhart
 * @author     Heath Barnhart hbarnhart@kanren.net
 */

namespace LibreNMS\Snmptrap\Handlers;

class XklTrapUtil
{
    /**
     * Get the value, applies the unit divsor from MIB, and returns float with units
     *
     * @param  string  $value
     * @return string $value
     */
    public static function removeUnits($value)
    {
        if ($value) {
            preg_match('/(-?\d+)\s(\d+)\/(\d+)\s(\S*)/', $value, $matches);
            $base = (int) $matches[1];
            $numerator = (int) $matches[2];
            $denominator = (int) $matches[3];
            $unit = trim($matches[4]);

            $multiplier = $numerator / $denominator;

            $value = ($base * $multiplier) . " $unit";
        } else {
            $value = 'N/A';
        }

        return $value;
    }
}
