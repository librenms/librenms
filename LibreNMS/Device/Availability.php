<?php
/**
 * Availability.php
 *
 * Availability calculation
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
 * @copyright  2020 Thomas Berberich
 * @author     Thomas Berberich <sourcehhdoctor@gmail.com>
 */

namespace LibreNMS\Device;

use \App\Models\DeviceOutage;

class Availability
{
    /*
     * 1 day     1 * 24 * 60 * 60 =    86400
     * 1 week    7 * 24 * 60 * 60 =   604800
     * 1 month  30 * 24 * 60 * 60 =  2592000
     * 1 year  365 * 24 * 60 * 60 = 31536000
     */

    public static function day($device, $precision = 3)
    {
        $duration = 86400;
        return self::availability($device, $duration, $precision);
    }

    public static function week($device, $precision = 3)
    {
        $duration = 604800;
        return self::availability($device, $duration, $precision);
    }

    public static function month($device, $precision = 3)
    {
        $duration = 2592000;
        return self::availability($device, $duration, $precision);
    }

    public static function year($device, $precision = 3)
    {
        $duration = 31536000;
        return self::availability($device, $duration, $precision);
    }

    /**
     * Get the availability of this device
     *
     * @param int $duration timeperiod in seconds
     * @param int $precision after comma precision
     * @return availability in %
     */
    public static function availability($device, $duration, $precision = 3)
    {
        if (!is_numeric($device['uptime'])) {
            return null;
        }

        $now = time();

        $query = DeviceOutage::where('device_id', '=', $device['device_id'])
            ->where('up_again', '>=', $now - $duration)
            ->orderBy('going_down');

        $found_outages = $query->get();

        # no recorded outages found, so use current uptime
        if (!count($found_outages)) {
            # uptime is greater duration interval -> full availability
            if ($device['uptime'] >= $duration) {
                return 100 * 1;
            } else {
                return round(100 * $device['uptime'] / $duration, $precision);
            }
        }

        $oldest_date_going_down = $query->first()->value('going_down');
        $oldest_uptime = $query->first()->value('uptime');
        $recorded_duration = $now - ($oldest_date_going_down - $oldest_uptime);

        if ($recorded_duration > $duration) {
            $recorded_duration = $duration;
        }

        # sum up time period of all outages
        $outage_summary = 0;
        foreach ($found_outages as $outage) {
            # if device is still down, outage goes till $now
            $up_again = $outage->up_again ?: $now;

            if ($outage->going_down >= ($now - $duration)) {
                # outage complete in duration period
                $going_down = $outage->going_down;
            } else {
                # outage partial in duration period, so consider only relevant part
                $going_down = $now - $duration;
            }
            $outage_summary += ($up_again - $going_down);
        }

        return round(100 * ($recorded_duration - $outage_summary) / $duration, $precision);
    }
}
