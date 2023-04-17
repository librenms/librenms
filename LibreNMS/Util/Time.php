<?php
/**
 * Time.php
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Util;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Carbon\CarbonInterval;

class Time
{
    public static function legacyTimeSpecToSecs(string $description): int
    {
        $conversion = [
            'now' => 0,
            'onehour' => 3600,
            'fourhour' => 14400,
            'sixhour' => 21600,
            'twelvehour' => 43200,
            'day' => 86400,
            'twoday' => 172800,
            'week' => 604800,
            'twoweek' => 1209600,
            'month' => 2678400,
            'twomonth' => 5356800,
            'threemonth' => 8035200,
            'year' => 31536000,
            'twoyear' => 63072000,
        ];

        return $conversion[$description] ?? 0;
    }

    /**
     * Format seconds as a human readable interval.  Negative seconds will say "ago".
     */
    public static function formatInterval(?int $seconds, bool $short = false, ?int $parts = null): string
    {
        if ($seconds == 0) {
            return '';
        }

        $parts = $parts ?? ($short ? 3 : -1);

        try {
            // handle negative seconds correctly
            if ($seconds < 0) {
                return CarbonInterval::seconds($seconds)->invert()->cascade()->forHumans([
                    'syntax' => CarbonInterface::DIFF_RELATIVE_TO_NOW,
                    'parts' => $parts,
                    'short' => $short,
                ]);
            }

            return CarbonInterval::seconds($seconds)->cascade()->forHumans([
                'syntax' => CarbonInterface::DIFF_ABSOLUTE,
                'parts' => $parts,
                'short' => $short,
            ]);
        } catch (\Exception) {
            return '';
        }
    }

    /**
     * Parse a time string into a timestamp including signed relative times using:
     * m - month
     * d - day
     * h - hour
     * y - year
     */
    public static function parseAt(string|int $time): int
    {
        if (is_numeric($time)) {
            return $time < 0 ? time() + $time : intval($time);
        }

        if (preg_match('/^[+-]\d+[hdmy]$/', $time)) {
            $units = [
                'm' => 60,
                'h' => 3600,
                'd' => 86400,
                'y' => 31557600,
            ];
            $value = Number::cast(substr($time, 1, -1));
            $unit = substr($time, -1);

            $offset = ($time[0] == '-' ? -1 : 1) * $units[$unit] * $value;

            return time() + $offset;
        }

        return (int) strtotime($time);
    }

    /**
     * Take a date and return the number of days from now
     */
    public static function dateToDays(string|int $date): int
    {
        $carbon = new Carbon();

        return $carbon->diffInDays($date, false);
    }
}
