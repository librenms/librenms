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
        // handle PHP8 change to implicit casting
        if (is_numeric($a) || is_numeric($b)) {
            $a = Number::cast($a);
            $b = is_array($b) ? $b : Number::cast($b);
        }

        switch ($comparison) {
            case '=':
                return $a == $b;
            case '!=':
                return $a != $b;
            case '==':
                return $a === $b;
            case '!==':
                return $a !== $b;
            case '>=':
                return $a >= $b;
            case '<=':
                return $a <= $b;
            case '>':
                return $a > $b;
            case '<':
                return $a < $b;
            case 'contains':
                return Str::contains($a, $b);
            case 'not_contains':
                return ! Str::contains($a, $b);
            case 'starts':
                return Str::startsWith($a, $b);
            case 'not_starts':
                return ! Str::startsWith($a, $b);
            case 'ends':
                return Str::endsWith($a, $b);
            case 'not_ends':
                return ! Str::endsWith($a, $b);
            case 'regex':
                return (bool) preg_match($b, $a);
            case 'not_regex':
                return ! ((bool) preg_match($b, $a));
            case 'in_array':
                return in_array($a, $b);
            case 'not_in_array':
                return ! in_array($a, $b);
            case 'exists':
                return isset($a) == $b;
            default:
                return false;
        }
    }
}
