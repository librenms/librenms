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
use LibreNMS\Exceptions\InsufficientDataException;
use LibreNMS\Exceptions\UncorrectableNegativeException;

class Number
{
    public static function formatBase($value, $base = 1000, $round = 2, $sf = 0, $suffix = 'B'): string
    {
        return $base == 1000
            ? self::formatSi($value, $round, $sf, $suffix)
            : self::formatBi($value, $round, $sf, $suffix);
    }

    private static function calcRound(float $value, int $round, int $sf): int
    {
        // If we want to track significat figures
        if ($sf) {
            $sfround = $sf;
            if ($value > 1) {
                // Get the number of digits to the left of the decimal
                $sflen = strlen(strval(intval($value)));

                if ($sflen >= $sf) {
                    // We have enough significant figures to the left of the decimal point, so we don't need anything to the right
                    $sfround = 0;
                } else {
                    // We can round one less for every digit to the left of the decimal place
                    $sfround -= $sflen;
                }
            }
            // If significant figures results in rounding to less decimal places, return this value
            if ($sfround < $round) {
                return $sfround;
            }
        }

        // Default
        return $round;
    }

    public static function formatSi($value, $round = 2, $sf = 0, $suffix = 'B'): string
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

        // Re-calculate rounding based on $sf before converting back to a negative value
        $round = self::calcRound($value, $round, $sf);

        if ($neg) {
            $value = $value * -1;
        }

        return self::cast(number_format($value, $round, '.', '')) . " $ext$suffix";
    }

    public static function formatBi($value, $round = 2, $sf = 0, $suffix = 'B'): string
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

        // Re-calculate rounding based on $sf before converting back to a negative value
        $round = self::calcRound($value, $round, $sf);

        if ($neg) {
            $value = $value * -1;
        }

        return self::cast(number_format($value, $round, '.', '')) . " $ext$suffix";
    }

    /**
     * Convert an Si or Bi formatted value to bytes (or bits)
     * Returns NAN for invalidly formatted strings.
     */
    public static function toBytes(string $formatted): int|float
    {
        if (preg_match('/^([\d.]+)\s?([kKMGTPEZY]?)(i?)([bB]\w*)?$/', $formatted, $matches)) {
            [, $number, $magnitude, $baseIndicator] = $matches;
            $base = $baseIndicator == 'i' ? 1024 : 1000;
            $exponent = ['k' => 1, 'K' => 1, 'M' => 2, 'G' => 3, 'T' => 4, 'P' => 5, 'E' => 6, 'Z' => 7, 'Y' => 8];

            return self::cast($number) * pow($base, $exponent[$magnitude] ?? 0);
        }

        return NAN;
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
        // ensure total is strict positive and part is positive
        if ($total <= 0 || $part < 0) {
            return 0;
        }

        return round($part / $total * 100, $precision);
    }

    public static function constrainInteger(int $value, IntegerType $integerSize): int
    {
        if ($integerSize->isSigned()) {
            $maxSignedValue = $integerSize->maxValue();

            if ($value > $maxSignedValue) {
                $signedValue = $value - $maxSignedValue * 2 - 2;

                // if conversion was successful, the number will still be in the valid range
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

    /**
     * If a number is less than 0, assume it has overflowed 32bit INT_MAX
     * And try to correct the value by adding INT_MAX
     *
     * @param  int|null  $value  The value to correct
     * @param  int|null  $max  an upper bounds on the corrected value
     * @return int|null
     *
     * @throws UncorrectableNegativeException
     */
    public static function correctIntegerOverflow(mixed $value, ?int $max = null): int|null
    {
        if ($value === null) {
            return null;
        }

        $int_max = 4294967296;
        if ($value < 0) {
            // assume unsigned/signed issue
            $value = $int_max + self::cast($value);
            if (($max !== null && $value > $max) || $value > $int_max) {
                throw new UncorrectableNegativeException;
            }
        }

        return (int) $value;
    }

    /**
     * Supply a minimum of two of the four values and the others will be filled.
     *
     * @throws InsufficientDataException
     */
    public static function fillMissingRatio(mixed $total = null, mixed $used = null, mixed $available = null, mixed $used_percent = null, int $precision = 2, int|float $multiplier = 1): array
    {
        // fix out of bounds percent
        if ($used_percent) {
            $used_percent = self::normalizePercent($used_percent);
        }

        // total is missing try to calculate it
        if (! is_numeric($total)) {
            if (isset($used, $available)) {
                $total = $used + $available;
            } elseif ($used && $used_percent) {
                $total = $used / ($used_percent / 100);
            } elseif ($available && $used_percent) {
                $total = $available / (1 - ($used_percent / 100));
            } elseif (is_numeric($used_percent)) {
                $total = 100; // only have percent, mark total as 100
            }
        }

        if (! is_numeric($total) || ($used === null && $available === null && ! is_numeric($used_percent))) {
            throw new InsufficientDataException('Unable to calculate missing ratio values, not enough information. ' . json_encode(get_defined_vars()));
        }

        // fill used if it is missing
        $used_is_null = $used === null;
        if ($used_is_null) {
            $used = $available !== null
                ? $total - $available
                : $total * ($used_percent ? ($used_percent / 100) : 0);
        }

        // fill percent if it is missing
        if ($used_percent == null) {
            $used_percent = static::calculatePercent($used, $total, $precision);
        }

        // fill available if it is missing
        if ($available === null) {
            $available = $used_is_null
                ? $total * (1 - ($used_percent / 100))
                : $total - $used;
        }

        // return nicely formatted values
        return [
            round($total * $multiplier, $precision),
            round($used * $multiplier, $precision),
            round($available * $multiplier, $precision),
            round($used_percent, $precision),
        ];
    }

    /**
     * Handle a value that should be a percent, but is > 100 and assume it is off by some factor of 10
     */
    public static function normalizePercent(mixed $percent): float
    {
        $percent = floatval($percent);

        while ($percent > 100) {
            $percent = $percent / 10;
        }

        return $percent;
    }
}
