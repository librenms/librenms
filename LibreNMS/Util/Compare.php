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

class Compare
{
    /**
     * Perform comparison of two items based on give comparison method
     * Valid comparisons: =, !=, ==, !==, >=, <=, >, <, contains, starts, ends, regex
     * contains, starts, ends: $a haystack, $b needle(s)
     * regex: $a subject, $b regex
     */
    public static function values(int|bool|float|string|null $a, int|bool|float|string|array|null $b, string $comparison = '='): bool
    {
        $result = match ($comparison) {
            '==' => $a === $b,
            '!==' => $a !== $b,
            'contains' => str_contains((string) $a, $b),
            'not_contains' => ! str_contains((string) $a, $b),
            'starts' => str_starts_with((string) $a, $b),
            'not_starts' => ! str_starts_with((string) $a, $b),
            'ends' => str_ends_with((string) $a, $b),
            'not_ends' => ! str_ends_with((string) $a, $b),
            'regex' => (bool) preg_match($b, (string) $a),
            'not_regex' => ! preg_match($b, (string) $a),
            'in_array' => in_array($a, $b),
            'not_in_array' => ! in_array($a, $b),
            'exists' => isset($a) == $b,
            default => null,
        };

        if ($result !== null) {
            return $result;
        }

        // handle PHP8 change to implicit casting
        if (is_numeric($a) || is_numeric($b)) {
            $a = Number::cast($a);
            $b = is_array($b) ? $b : Number::cast($b);
        }

        return match ($comparison) {
            '=' => $a == $b,
            '!=' => $a != $b,
            '>=' => $a >= $b,
            '<=' => $a <= $b,
            '>' => $a > $b,
            '<' => $a < $b,
            default => false,
        };
    }
}
