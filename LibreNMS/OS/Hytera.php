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
                $value = self::hexToFloat((string) $values[$sensor->sensor_id], 2);
                if ($value === null) {
                    unset($values[$sensor->sensor_id]);
                } else {
                    $values[$sensor->sensor_id] = $value;
                }
            }
        }

        return $values;
    }

    /**
     * Convert a little-endian IEEE-754 float, sent as hex bytes or as 4 raw bytes, to a number with the given decimals.
     * Null when the input is not 4 bytes.
     */
    public static function hexToFloat(string $number, int $decimals): ?string
    {
        // only surrounding quotes, raw bytes can contain a quote
        if (strlen($number) != 4 && strlen($number) >= 2 && $number[0] == '"' && $number[-1] == '"') {
            $number = substr($number, 1, -1);
        }

        $hex = str_replace(' ', '', $number);
        if (strlen($number) == 4) {
            $bytes = $number;
        } elseif (strlen($hex) == 8 && ctype_xdigit($hex)) {
            $bytes = (string) hex2bin($hex);
        } else {
            return null;
        }

        return number_format(unpack('g', $bytes)[1], $decimals, '.', '');
    }
}
