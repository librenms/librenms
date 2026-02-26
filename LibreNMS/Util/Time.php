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

use App\Facades\LibrenmsConfig;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Support\Facades\Config;

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

        try {
            return Carbon::now()->subSeconds(abs($seconds))->diffForHumans(
                syntax: $seconds < 0 ? CarbonInterface::DIFF_RELATIVE_TO_NOW : CarbonInterface::DIFF_ABSOLUTE,
                short: $short,
                parts: $parts ?? ($short ? 3 : 4),
            );
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
     * Parse flexible time input into a Unix timestamp (seconds).
     * Accepts:
     * - null/empty: returns null
     * - Numeric seconds or milliseconds since epoch
     * - Relative offsets like 6h, -1d, +2w, 1m, 1y (sign optional => defaults to past)
     * - Parsable date/time strings (Carbon::parse)
     * Returns null on invalid input.
     */
    public static function parseInput(string|int|null $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return Carbon::createFromTimestampUTC($value)->getTimestamp();
        }

        // adapt relative like 6h, -1d, +2m, 1y
        if (preg_match('/^([+-])?(\d+)([hdmwys]|mo)$/', $value, $matches)) {
            $sign = $matches[1] ?: '-';
            $unit = match ($matches[3]) {
                's' => 'second',
                'm' => 'minute',
                'h' => 'hour',
                'd' => 'day',
                'w' => 'week',
                'mo' => 'month',
                'y' => 'year',
            };

            $value = "$sign$matches[2] $unit";
        }

        try {
            return Carbon::parse($value)->getTimestamp();
        } catch (InvalidFormatException) {
            return null;
        }
    }

    /**
     * Take a date and return the number of days from now
     */
    public static function dateToMinutes(string|int $date): int
    {
        $carbon = new Carbon();

        return (int) $carbon->diffInMinutes($date);
    }

    public static function durationToSeconds(string $duration): int
    {
        if (preg_match('/(\d+)([mhd]?)/', $duration, $matches)) {
            $multipliers = [
                'm' => 60,
                'h' => 3600,
                'd' => 86400,
            ];

            $multiplier = $multipliers[$matches[2]] ?? 1;

            return $matches[1] * $multiplier;
        }

        return $duration === '' ? 0 : 300;
    }

    /**
     * Return a random time between the two given times.
     */
    public static function randomBetween(string|int $min, string|int $max): Carbon
    {
        $time = new Carbon($min);

        $time->addSeconds(mt_rand(0, (int) $time->diffInSeconds(new Carbon($max), true)));

        return $time;
    }

    /**
     * Return a psedudo random time between the two given times.
     * The same time will always be returned for a given APP_KEY
     */
    public static function pseudoRandomBetween(string|int $min, string|int $max, string $format = 'H:i'): string
    {
        // Seed the random number generator to get consistent results for a given APP_KEY
        mt_srand(crc32(Config::get('app.key') . $min . $max));

        $time = self::randomBetween($min, $max);

        // Need to restore the seed after
        mt_srand();

        return $time->format($format);
    }

    /**
     * Format a timestamp for display to users in their selected timezone
     */
    public static function format(Carbon|string|int $input, string $format): string
    {
        if (is_string($input)) {
            $input = Carbon::parse($input);
        } elseif (is_numeric($input)) {
            $input = Carbon::createFromTimestamp($input);
        }

        $format = match ($format) {
            'long', 'compact', 'byminute', 'time' => LibrenmsConfig::get("dateformat.$format"),
            default => throw new \Exception('Format needs to be one of log, compact, byminute or time'),
        };

        return $input->setTimezone(session('preferences.timezone'))->format($format);
    }
}
