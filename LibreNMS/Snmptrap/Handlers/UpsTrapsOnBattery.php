<?php
/**
 * UpsTrapsOnBattery.php
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
 * @author     TheGreatDoc
 */

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use Illuminate\Support\Str;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;
use Log;

class UpsTrapsOnBattery implements SnmptrapHandler
{
    /**
     * Handle snmptrap.
     * Data is pre-parsed and delivered as a Trap.
     *
     * @param Device $device
     * @param Trap $trap
     * @return void
     */
    public function handle(Device $device, Trap $trap)
    {
        $min_remaining = Str::before($trap->getOidData($trap->findOid('UPS-MIB::upsEstimatedMinutesRemaining.0')), ' ');
        $sec_time = Str::before($trap->getOidData($trap->findOid('UPS-MIB::upsSecondsOnBattery.0')), ' ');
        Log::event("UPS running on battery for $sec_time seconds. Estimated $min_remaining minutes remaining", $device->device_id, 'trap', 5);
        $sensor_remaining = $device->sensors()->where('sensor_index', '200')->where('sensor_type', 'rfc1628')->first();
        if (! $sensor_remaining) {
            Log::warning("Snmptrap UpsTraps: Could not find matching sensor \'Estimated battery time remaining\' for device: " . $device->hostname);

            return;
        }
        $sensor_remaining->sensor_current = $min_remaining / $sensor_remaining->sensor_divisor;
        $sensor_remaining->save();

        $sensor_time = $device->sensors()->where('sensor_index', '100')->where('sensor_type', 'rfc1628')->first();
        if (! $sensor_time) {
            Log::warning("Snmptrap UpsTraps: Could not find matching sensor \'Time on battery\' for device: " . $device->hostname);

            return;
        }
        $sensor_time->sensor_current = $sec_time / $sensor_time->sensor_divisor;
        $sensor_time->save();

        $sensor_output = $device->sensors()->where('sensor_type', 'upsOutputSourceState')->first();
        if (! $sensor_output) {
            Log::warning("Snmptrap UpsTraps: Could not find matching sensor \'upsOutputSourceState\' for device: " . $device->hostname);

            return;
        }
        $sensor_output->sensor_current = 5;
        $sensor_output->save();
    }
}
