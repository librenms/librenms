<?php
/**
 * OccamThresholdExceededNotification.php
 *
 * Handles the following traps:
 * - OCCAM-KERNEL-MIB::thresholdExceededNotification
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

class OccamThresholdExceededNotification implements SnmptrapHandler
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
        $alarmState = $thresholdEntity = $thresholdValue = "unknown";

        if ($trap_oid = $trap->findOid('OCCAM-KERNEL-MIB::alarmState'))
            $alarmState = $trap->getOidData($trap_oid);

        if ($trap_oid = $trap->findOid('OCCAM-KERNEL-MIB::thresholdEntity'))
            $thresholdEntity = $trap->getOidData($trap_oid);

        if ($trap_oid = $trap->findOid('OCCAM-KERNEL-MIB::thresholdValue'))
            $thresholdValue = $trap->getOidData($trap_oid);

        Log::event("Threshold Exceeded: alarm state $alarmState for entity (MAC address / multicast group) $thresholdEntity: threshold value $thresholdValue", $device->device_id, 'trap', 4);
    }
}
