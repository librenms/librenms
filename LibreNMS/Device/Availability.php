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
 *
 * @copyright  2020 Thomas Berberich
 * @author     Thomas Berberich <sourcehhdoctor@gmail.com>
 */

namespace LibreNMS\Device;

use App\Models\Device;
use App\Models\DeviceOutage;
use Illuminate\Support\Collection;
use LibreNMS\Util\Number;

class Availability
{
    /*
     * 1 day     1 * 24 * 60 * 60 =    86400
     * 1 week    7 * 24 * 60 * 60 =   604800
     * 1 month  30 * 24 * 60 * 60 =  2592000
     * 1 year  365 * 24 * 60 * 60 = 31536000
     */

    public static function day(Device $device, int $precision = 3): float
    {
        $duration = 86400;

        return self::availability($device, $duration, $precision);
    }

    public static function week(Device $device, int $precision = 3): float
    {
        $duration = 604800;

        return self::availability($device, $duration, $precision);
    }

    public static function month(Device $device, int $precision = 3): float
    {
        $duration = 2592000;

        return self::availability($device, $duration, $precision);
    }

    public static function year(Device $device, int $precision = 3): float
    {
        $duration = 31536000;

        return self::availability($device, $duration, $precision);
    }

    /**
     * addition of all recorded outages in seconds
     *
     * @param  Collection<DeviceOutage>  $found_outages  filtered database object with all recorded outages
     * @param  int  $duration  time period to calculate for
     * @param  int  $now  timestamp for 'now'
     * @return int sum of all matching outages in seconds
     */
    protected static function outageSummary(Collection $found_outages, int $duration, int $now): int
    {
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
     * @param  Device  $device  device to be looked at
     * @param  int  $duration  time period to calculate for
     * @param  int  $precision  float precision for calculated availability
     * @return float calculated availability
     */
    public static function availability(Device $device, int $duration, int $precision = 3): float
    {
        $now = time();

        $found_outages = $device->outages()->where('up_again', '>=', $now - $duration)
            ->orderBy('going_down')->get();

        // no recorded outages found, so use current status
        if ($found_outages->isEmpty()) {
            return 100 * $device->status;
        }

        // don't calculate for time when the device didn't exist
        if ($device->inserted) {
            $duration = min($duration, $device->inserted->diffInSeconds());
        }

        $outage_summary = self::outageSummary($found_outages, $duration, $now);

        return Number::calculatePercent($duration - $outage_summary, $duration, $precision);
    }
}
