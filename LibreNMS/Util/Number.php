<?php
/*
 * Number.php
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
 * @package    LibreNMS
 * @link       https://www.librenms.org
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Util;

use LibreNMS\Enum\IntegerType;

class Number
{
    public static function formatBase($value, $base = 1000, $round = 2, $sf = 3, $suffix = 'B')
    {
        return $base == 1000
            ? self::formatSi($value, $round, $sf, $suffix)
            : self::formatBi($value, $round, $sf, $suffix);
    }

    public static function formatSi($value, $round = 2, $sf = 3, $suffix = 'B')
    {
        $value = (float) $value;
        $neg = $value < 0;
        if ($neg) {
            $value = $value * -1;
        }

        if ($value >= '0.1') {
            $sizes = ['', 'k', 'M', 'G', 'T', 'P', 'E', 'Z', 'Y'];
            $ext = $sizes[0];
            for ($i = 1; ($i < count($sizes)) && ($value >= 1000); $i++) {
                $value = $value / 1000;
                $ext = $sizes[$i];
            }
        } else {
            $sizes = ['', 'm', 'u', 'n', 'p'];
            $ext = $sizes[0];
            for ($i = 1; ($i < count($sizes)) && ($value != 0) && ($value <= 0.1); $i++) {
                $value = $value * 1000;
                $ext = $sizes[$i];
            }
        }

        if ($neg) {
            $value = $value * -1;
        }

        return self::cast(number_format(round($value, $round), $sf, '.', '')) . " $ext$suffix";
    }

    public static function formatBi($value, $round = 2, $sf = 3, $suffix = 'B')
    {
        $value = (float) $value;
        $neg = $value < 0;
        if ($neg) {
            $value = $value * -1;
        }
        $sizes = ['', 'Ki', 'Mi', 'Gi', 'Ti', 'Pi', 'Ei', 'Zi', 'Yi'];
        $ext = $sizes[0];
        for ($i = 1; ($i < count($sizes)) && ($value >= 1024); $i++) {
            $value = $value / 1024;
            $ext = $sizes[$i];
        }

        if ($neg) {
            $value = $value * -1;
        }

        return self::cast(number_format(round($value, $round), $sf, '.', '')) . " $ext$suffix";
    }

    /**
     * Convert an Si or Bi formatted value to bytes (or bits)
     */
    public static function toBytes(string $formatted): int|float
    {
        preg_match('/^([\d.]+)([KMGTPEZY]?)(\w?)\w?$/', $formatted, $matches);
        [, $number, $magnitude, $baseIndicator] = $matches;
        $base = $baseIndicator == 'i' ? 1024 : 1000;
        $exponent = ['K' => 1, 'M' => 2, 'G' => 3, 'T' => 4, 'P' => 5, 'E' => 6, 'Z' => 7, 'Y' => 8];

        return self::cast($number) * pow($base, $exponent[$magnitude] ?? 0);
    }

    /**
     * Cast string to int or float.
     * Returns 0 if string is not numeric
     *
     * @param  mixed  $number
     * @return float|int
     */
    public static function cast(mixed $number): float|int
    {
        if (! is_numeric($number)) {
            // match pre-PHP8 behavior
            if (! preg_match('/^\s*-?\d+(\.\d+)?/', $number ?? '', $matches)) {
                return 0;
            }
            $number = $matches[0];
        }

        $float = (float) $number;
        $int = (int) $number;

        return $float == $int ? $int : $float;
    }

    /**
     * Extract the first number found from a string
     */
    public static function extract(mixed $string): float|int
    {
        if (! is_numeric($string)) {
            preg_match('/-?\d*\.?\d+/', $string, $matches);
            if (! empty($matches[0])) {
                $string = $matches[0];
            }
        }

        return self::cast($string);
    }

    /**
     * Calculate a percent, but make sure to not divide by zero.  In that case, return 0.
     *
     * @param  int|float  $part
     * @param  int|float  $total
     * @param  int  $precision
     * @return float
     */
    public static function calculatePercent($part, $total, int $precision = 2): float
    {
        if ($total == 0) {
            return 0;
        }

        return round($part / $total * 100, $precision);
    }

    /**
     * This converts a memory size containing the unit to bytes. example 1 MiB to 1048576 bytes
     */
    public static function convertToBytes(string $from): ?int
    {
        $units = ['B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB'];
        $number = floatval(substr($from, 0, -3));
        $suffix = substr($from, -3);

        //B or no suffix
        if (is_numeric(substr($suffix, 0, 1))) {
            return (int) $from;
        }

        $exponent = array_flip($units)[$suffix] ?? null;
        if ($exponent === null) {
            return null;
        }

        return (int) ($number * (1024 ** $exponent));
    }

    public static function constrainInteger(int $value, IntegerType $integerSize): int
    {
        if ($integerSize->isSigned()) {
            $maxSignedValue = $integerSize->maxValue();

            if ($value > $maxSignedValue) {
                $signedValue = $value - $maxSignedValue * 2 - 2;

                // if conversion was successfull, the number will still be in the valid range
                if ($signedValue > $maxSignedValue) {
                    throw new \InvalidArgumentException('Unsigned value exceeds the maximum representable value of ' . $integerSize->name);
                }

                return $signedValue;
            }

            return $value;
        }

        // unsigned check if value is negative
        if ($value < 0) {
            $unsignedValue = $value + $integerSize->maxValue() - 1;

            if ($unsignedValue < 0) {
                throw new \InvalidArgumentException('Unsigned value exceeds the minimum representable value of ' . $integerSize->name);
            }

            return $unsignedValue;
        }

        return $value;
    }
}
