<?php
/**
 * OccamSensorExtAlarmRaised.php
 *
 * Handles the OCCAM-SENSOR-MIB::occamExtAlarmRaised trap
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
 * @package    LibreNMS
 * @link       https://www.librenms.org
 *
 * @copyright  2021 Vantage Point Solutions
 * @author     Eric Graham <eric.graham@vantagepnt.com>
 */

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;
use Log;

class OccamSensorExtAlarmRaised implements SnmptrapHandler
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
        if ($trap_oid = $trap->findOid('OCCAM-SENSOR-MIB::occamExtAlarmDescription')) {
            $alarmDescription = $trap->getOidData($trap_oid);
            Log::event("External Alarm Raised: $alarmDescription", $device->device_id, 'trap', 4);
        } else {
            Log::event("External Alarm Raised: No Description (malformed MIB)");
        }
    }
}
