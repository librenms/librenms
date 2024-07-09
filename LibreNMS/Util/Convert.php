<?php
/*
 * Convert.php
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2024 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Util;

class Convert
{
    public static function fahrenheitToCelsius(int|float|string|null $fahrenheit): float
    {
        return ($fahrenheit - 32) / 1.8;
    }

    public static function celsiusToFahrenheit(int|float|string|null $celsius): float
    {
        return $celsius * 1.8 + 32;
    }

    public static function uwToDbm(int|float|string|null $microWatt): float|int
    {
        return $microWatt == 0 ? -60 : 10 * log10($microWatt / 1000);
    }

    public static function mwToDbm(int|float|string|null $milliWatt): float|int
    {
        return $milliWatt == 0 ? -60 : 10 * log10($milliWatt);
    }
}
