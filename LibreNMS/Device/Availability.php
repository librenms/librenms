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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2020 Thomas Berberich
 * @author     Thomas Berberich <sourcehhdoctor@gmail.com>
 */

namespace LibreNMS\Device;

use App\Models\DeviceOutage;

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
     * addition of all recorded outages in seconds
     *
     * @param object $found_outages filtered database object with all recorded outages
     * @param int $duration time period to calculate for
     * @param int $now timestamp for 'now'
     * @return int sum of all matching outages in seconds
     */
    protected static function outageSummary($found_outages, $duration, $now = null)
    {
        if (! is_numeric($now)) {
            $now = time();
        }

        // sum up time period of all outages
        $outage_sum = 0;
        foreach ($found_outages as $outage) {
            // if device is still down, outage goes till $now
            $up_again = $outage->up_again ?: $now;

            if ($outage->going_down >= ($now - $duration)) {
                // outage complete in duration period
                $going_down = $outage->going_down;
            } else {
                // outage partial in duration period, so consider only relevant part
                $going_down = $now - $duration;
            }
            $outage_sum += ($up_again - $going_down);
        }

        return $outage_sum;
    }

    /**
     * Get the availability (decreasing) of this device
     * means, starting with 100% as default
     * substracts recorded outages
     *
     * @param array $device device to be looked at
     * @param int $duration time period to calculate for
     * @param int $precision float precision for calculated availability
     * @return float calculated availability
     */
    public static function availability($device, $duration, $precision = 3, $now = null)
    {
        if (! is_numeric($now)) {
            $now = time();
        }

        $query = DeviceOutage::where('device_id', '=', $device['device_id'])
            ->where('up_again', '>=', $now - $duration)
            ->orderBy('going_down');

        $found_outages = $query->get();

        // no recorded outages found, so use current uptime
        if (! count($found_outages)) {
            return 100 * 1;
        }

        $outage_summary = self::outageSummary($found_outages, $duration, $now);

        return round(100 * ($duration - $outage_summary) / $duration, $precision);
    }
}
