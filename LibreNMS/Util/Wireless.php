<?php
/**
 * Wireless.php
 *
 * Utilities for wireless
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Util;

class Wireless
{
    /**
     * Convert a WiFi channel to a Frequency in MHz
     *
     * @param $channel
     * @return int
     */
    public static function channelToFrequency($channel)
    {
        $channels = array(
            1 => 2412,
            2 => 2417,
            3 => 2422,
            4 => 2427,
            5 => 2432,
            6 => 2437,
            7 => 2442,
            8 => 2447,
            9 => 2452,
            10 => 2457,
            11 => 2462,
            12 => 2467,
            13 => 2472,
            14 => 2484,
            34 => 5170,
            36 => 5180,
            38 => 5190,
            40 => 5200,
            42 => 5210,
            44 => 5220,
            46 => 5230,
            48 => 5240,
            52 => 5260,
            56 => 5280,
            60 => 5300,
            64 => 5320,
            100 => 5500,
            104 => 5520,
            108 => 5540,
            112 => 5560,
            116 => 5580,
            120 => 5600,
            124 => 5620,
            128 => 5640,
            132 => 5660,
            136 => 5680,
            140 => 5700,
            149 => 5745,
            153 => 5765,
            157 => 5785,
            161 => 5805,
            165 => 5825,
        );

        return $channels[$channel];
    }
}
