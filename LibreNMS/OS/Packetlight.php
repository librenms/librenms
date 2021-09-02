<?php
/**
 * Packetlight.php
 *
 * Packetlight
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
 * @copyright  2019 PipoCanaja
 * @author     PipoCanaja
 */

namespace LibreNMS\OS;

use LibreNMS\OS;

class Packetlight extends OS
{
    /**
     * Subtract 30 (for yaml user_func)
     */
    public static function offsetSfpDbm($value)
    {
        return $value - 30;
    }

    /**
     * Subtract 128 (for yaml user_func)
     */
    public static function offsetSfpTemperature($value)
    {
        return $value - 128;
    }

    /**
     * Convert Watts 10e-7 to Dbm
     */
    public static function convertWattToDbm($value)
    {
        return 10 * log10($value / 10000000) + 30;
    }
}
