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
     * contains, starts, ends: $a haystack, $b needle(s)
     * regex: $a subject, $b regex
     *
     * @param  mixed  $a
     * @param  mixed  $b
     * @param  string  $comparison  =, !=, ==, !== >=, <=, >, <, contains, starts, ends, regex
     * @return bool
     */
    public static function values($a, $b, $comparison = '=')
    {
        $numeric_comparisons = ['=', '!=', '==', '!==', '>=', '<=', '>', '<'];

        if (in_array($comparison, $numeric_comparisons)) {
            // handle PHP8 change to implicit casting
            if (is_numeric($a) || is_numeric($b)) {
                $a = Number::cast($a);
                $b = is_array($b) ? $b : Number::cast($b);
            }
        }

        return match ($comparison) {
            '=' => $a == $b,
            '!=' => $a != $b,
            '==' => $a === $b,
            '!==' => $a !== $b,
            '>=' => $a >= $b,
            '<=' => $a <= $b,
            '>' => $a > $b,
            '<' => $a < $b,
            'contains' => Str::contains($a, $b),
            'not_contains' => ! Str::contains($a, $b),
            'starts' => Str::startsWith($a, $b),
            'not_starts' => ! Str::startsWith($a, $b),
            'ends' => Str::endsWith($a, $b),
            'not_ends' => ! Str::endsWith($a, $b),
            'regex' => Str::isMatch($b, $a),
            'not_regex' => ! Str::isMatch($b, $a),
            'in_array' => in_array($a, $b),
            'not_in_array' => ! in_array($a, $b),
            'exists' => isset($a) == $b,
            default => false,
        };
    }
}
