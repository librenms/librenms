<?php

/**
 * UserFuncHelper.php
 *
 * Helper class for "user_func"
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
 */

namespace LibreNMS\Util;

use Illuminate\Support\Str;
use LibreNMS\Exceptions\UserFunctionExistException;

class UserFuncHelper
{
    /**
     * Sensors store these conversions by the name of the global function they used to call
     */
    private const array LEGACY_NAMES = [
        'fahrenheit_to_celsius' => 'fahrenheitToCelsius',
        'kelvin_to_celsius' => 'kelvinToCelsius',
        'string_to_float' => 'stringToFloat',
        'uw_to_dbm' => 'uwToDbm',
        'mw_to_dbm' => 'mwToDbm',
        'ieee754_to_decimal' => 'ieee754ToDecimal',
    ];

    /**
     * @param  array<string, mixed>  $sensor
     */
    public function __construct(
        public string|int|float $value,
        public string|int|float|null $value_raw = null,
        public array $sensor = [],
    ) {
    }

    /**
     * Convert a sensor value with its user_func: a method of this class, a static method (Class::method)
     * that also gets the raw value and the sensor, or a function that gets the value.
     *
     * @param  array<string, mixed>  $sensor
     *
     * @throws UserFunctionExistException
     */
    public static function apply(string $function, int|float $value, string|int|float|null $raw = null, array $sensor = []): mixed
    {
        $method = self::LEGACY_NAMES[$function] ?? $function;

        if (method_exists(self::class, $method)) {
            return new self($value, $raw, $sensor)->$method();
        }

        if (! is_callable($function)) {
            throw new UserFunctionExistException("Invalid user function: $function");
        }

        return str_contains($function, '::') ? $function($value, $raw, $sensor) : $function($value);
    }

    public function __call(string $name, array $arguments): mixed
    {
        throw new UserFunctionExistException("Invalid user function: $name");
    }

    public function fahrenheitToCelsius(): float
    {
        return round(($this->value - 32) / 1.8, 2);
    }

    public function kelvinToCelsius(): float
    {
        return round($this->value - 273.15, 2);
    }

    public function stringToFloat(): float
    {
        return round((float) $this->value, 2);
    }

    /**
     * Convert microwatts to dBm, -60 for 0 and null for negative values
     */
    public function uwToDbm(): int|float|null
    {
        return $this->value < 0 ? null : ($this->value == 0 ? -60 : 10 * log10($this->value / 1000));
    }

    /**
     * Convert milliwatts to dBm, -60 for 0 and null for negative values
     */
    public function mwToDbm(): int|float|null
    {
        return $this->value < 0 ? null : ($this->value == 0 ? -60 : 10 * log10($this->value));
    }

    /**
     * Convert the integer representation of an IEEE 754 32-bit float to its value
     */
    public function ieee754ToDecimal(): float
    {
        return unpack('G', pack('N', (int) $this->value))[1];
    }

    public function dateToRuntime(): int
    {
        return Time::dateToMinutes($this->value_raw);
    }

    public function fsParseChannelValue(): float
    {
        $channel = Str::afterLast($this->sensor['sensor_index'], '.');

        return Number::cast(explode(',', $this->value_raw)[$channel] ?? '') * $this->sensor['sensor_multiplier'] / $this->sensor['sensor_divisor'];
    }

    public function hhmmssToMinutes(): int
    {
        [$h, $m, $s] = explode(':', $this->value_raw);

        return (int) ((int) $h * 60 + (int) $m + (int) $s / 60);
    }

    /**
     * Reduce a bitmap to its highest set bit, e.g. 24 (16 + 8) becomes 16
     */
    public function highestBit(): int
    {
        $value = (int) $this->value;

        return $value > 0 ? 1 << (strlen(decbin($value)) - 1) : 0;
    }
}
