<?php

/**
 * Color.php
 *
 * Misc color generation
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Util;

class Color
{
    public static function toner(string $descr, int $percent): array
    {
        $color = self::percentage(100 - $percent, null);

        if (str_ends_with($descr, 'C')
            || stripos($descr, 'cyan') !== false) {
            $color['left'] = '55D6D3';
            $color['right'] = '33B4B1';
        }

        if (str_ends_with($descr, 'M')
            || stripos($descr, 'magenta') !== false) {
            $color['left'] = 'F24AC8';
            $color['right'] = 'D028A6';
        }

        if (str_ends_with($descr, 'Y')
            || stripos($descr, 'yellow') !== false
            || stripos($descr, 'giallo') !== false
            || stripos($descr, 'gul') !== false
        ) {
            $color['left'] = 'FFF200';
            $color['right'] = 'DDD000';
        }

        if (str_ends_with($descr, 'K')
            || stripos($descr, 'black') !== false
            || stripos($descr, 'nero') !== false
        ) {
            $color['left'] = '000000';
            $color['right'] = '222222';
        }

        return $color;
    }

    /**
     * Get colors for a percentage bar based on current percentage
     *
     * @param  int|float  $percentage
     * @param  int|float  $component_perc_warn
     * @return string[]
     */
    public static function percentage(int|float $percentage, int|float|null $component_perc_warn = null, string $prefix = ''): array
    {
        $perc_warn = 75;

        if (isset($component_perc_warn)) {
            $perc_warn = round($component_perc_warn, 0);
        }

        if ($percentage > $perc_warn) {
            return [
                'left' => $prefix . 'c4323f',
                'right' => $prefix . 'c96a73',
                'middle' => $prefix . 'c75862',
            ];
        }

        if ($percentage > 75) {
            return [
                'left' => $prefix . 'bf5d5b',
                'right' => $prefix . 'eaeaea',
                'middle' => $prefix . 'c97e7d',
            ];
        }

        if ($percentage > 50) {
            return [
                'left' => $prefix . 'bf875b',
                'right' => $prefix . 'eaeaea',
                'middle' => $prefix . 'cca07e',
            ];
        }

        if ($percentage > 25) {
            return [
                'left' => $prefix . '5b93bf',
                'right' => $prefix . 'eaeaea',
                'middle' => $prefix . '7da8c9',
            ];
        }

        return [
            'left' => $prefix . '9abf5b',
            'right' => $prefix . 'eaeaea',
            'middle' => $prefix . 'afcc7c',
        ];
    }

    public static function percent(int|float|null $numerator = null, int|float|null $denominator = null, int|float|null $percent = null): string
    {
        $percent = $percent ? round($percent) : Number::calculatePercent($numerator, $denominator, 0);
        $r = max(0, min(255, 5 * ($percent - 25)));
        $b = max(0, 255 - (5 * ($percent + 25)));

        return sprintf('#%02x%02x%02x', $r, $b, $b);
    }
}
