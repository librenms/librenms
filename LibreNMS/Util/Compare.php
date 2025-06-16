<?php

/**
 * Compare.php
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
 * @link       https://www.librenms.org
 *
 * @copyright  2022 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Util;

use Illuminate\Support\Str;

class Compare
{
    /**
     * Perform comparison of two items based on give comparison method
     * Valid comparisons: =, !=, ==, !==, >=, <=, >, <, contains, starts, ends, regex
     * contains, starts, ends: $a haystack, $b needle(s) - if $b is array, checks if any needle matches
     * regex: $a subject, $b regex(es) - if $b is array, checks if any regex matches
     */
    public static function values(int|bool|float|string|null $a, int|bool|float|string|array|null $b, string $comparison = '='): bool
    {
        $result = match ($comparison) {
            'contains' => Str::contains((string) $a, $b),
            'not_contains' => ! Str::contains((string) $a, $b),
            'starts' => Str::startsWith((string) $a, $b),
            'not_starts' => ! Str::startsWith((string) $a, $b),
            'ends' => Str::endsWith((string) $a, $b),
            'not_ends' => ! Str::endsWith((string) $a, $b),
            'regex' => Str::isMatch($b, (string) $a),
            'not_regex' => ! Str::isMatch($b, (string) $a),
            'exists' => isset($a) == $b,
            default => null,
        };

        if ($result !== null) {
            return $result;
        }

        // handle PHP8 change to implicit casting
        if (is_numeric($b) && $a !== null && ! is_bool($a) && is_numeric($cast_a = Number::cast($a))) {
            $a = $cast_a;
            $b = Number::cast($b);
        }

        return match ($comparison) {
            '==' => $a === $b,
            '!==' => $a !== $b,
            '=' => $a == $b,
            '!=' => $a != $b,
            '>=' => $a >= $b,
            '<=' => $a <= $b,
            '>' => $a > $b,
            '<' => $a < $b,
            'in_array' => in_array($a, $b),
            'not_in_array' => ! in_array($a, $b),
            default => false,
        };
    }
}
