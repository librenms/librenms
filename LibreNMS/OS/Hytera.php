<?php

/**
 * Hytera.php
 *
 * Hytera
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

namespace LibreNMS\OS;

use Illuminate\Database\Eloquent\Collection;
use LibreNMS\OS;
use LibreNMS\Util\StringHelpers;

class Hytera extends OS
{
    /**
     * Temperatures and voltages are floats in hex strings
     */
    public function pollSensors(Collection $sensors): array
    {
        $values = parent::pollSensors($sensors);

        foreach ($sensors->whereIn('sensor_class', ['temperature', 'voltage']) as $sensor) {
            if (isset($values[$sensor->sensor_id])) {
                $values[$sensor->sensor_id] = self::hexToFloat((string) $values[$sensor->sensor_id], 2);
            }
        }

        return $values;
    }

    /**
     * Convert a float sent as reversed hex bytes (or 4 raw bytes) to a number with the given decimals
     */
    public static function hexToFloat(string $number, int $decimals): string
    {
        $number = str_replace('"', '', $number);
        if (strlen(str_replace(' ', '', $number)) == 4) {
            $number = StringHelpers::asciiToHex($number, ' ');
        }

        $hex = substr(implode('', array_reverse(explode(' ', $number))), 0, -1);
        $bits = '';
        foreach (str_split($hex) as $digit) {
            $bits .= sprintf('%04d', decbin(hexdec($digit)));
        }

        $exponent = bindec(substr($bits, 1, 8)) - 127;
        $mantissa = substr($bits, 9);
        $fraction = substr($mantissa, $exponent);
        $integer = bindec('1' . substr($mantissa, 0, $exponent));

        $point = array_reverse(str_split($fraction));
        $length = strlen($fraction);
        $pointNumber = min(number_format(((int) ($point[0] ?? 0)) / 2, $length, '.', ''), 1);
        $value = '';
        for ($i = 1; $i < $length; $i++) {
            $value = number_format($pointNumber / 2, $length, '.', '');
            $pointNumber = ($point[$i + 1] ?? '') . substr($value, 1);
        }

        $float = $integer + (float) $value;

        return number_format(substr($bits, 0, 1) == 1 ? -$float : $float, $decimals, '.', '');
    }
}
