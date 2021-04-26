<?php
/**
 * Mgnt2TrapNMSEvent.php
 *
 * -Description-
 *
 * Ekinops managment module alarms
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
 * @copyright  2020 KanREN, Inc.
 * @author     Heath Barnhart <hbarnhart@kanren.net>
 */

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;
use Log;

class Mgnt2TrapNmsAlarm implements SnmptrapHandler
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
        $alarmObj = $trap->getOidData($trap->findOid('EKINOPS-MGNT2-NMS-MIB::mgnt2AlmLogObjectClassIdentifier'));
        $sourcePm = $trap->getOidData($trap->findOid('EKINOPS-MGNT2-NMS-MIB::mgnt2AlmLogSourcePm'));
        $slot = $trap->getOidData($trap->findOid('EKINOPS-MGNT2-NMS-MIB::mgnt2AlmLogBoardNumber'));
        $portType = $trap->getOidData($trap->findOid('EKINOPS-MGNT2-NMS-MIB::mgnt2AlmLogSourcePortType'));
        $portNum = $trap->getOidData($trap->findOid('EKINOPS-MGNT2-NMS-MIB::mgnt2AlmLogSourcePortNumber'));
        $probCause = $trap->getOidData($trap->findOid('EKINOPS-MGNT2-NMS-MIB::mgnt2AlmLogProbableCause'));
        $probSpecific = $trap->getOidData($trap->findOid('EKINOPS-MGNT2-NMS-MIB::mgnt2AlmLogSpecificProblem'));
        $probAdd = $trap->getOidData($trap->findOid('EKINOPS-MGNT2-NMS-MIB::mgnt2AlmLogAdditionalText'));
        $alarmSeverity = $trap->getOidData($trap->findOid('EKINOPS-MGNT2-NMS-MIB::mgnt2AlmLogSeverity'));

        // Adding additional info if it exists.
        if (! empty($probAdd)) {
            $probSpecific = "$probSpecific Additional info: $probAdd";
        }

        // Changing other to unknown
        if ($probCause == 'other') {
            $probCause = 'Unknown';
        }

        if ($alarmObj == 'port') {
            $msg = "Alarm on slot $slot, $sourcePm Port: $portType $portNum Issue: $probSpecific Possible Cause: $probCause";
        } else {
            $msg = "Alarm on slot $slot, $sourcePm Issue: $probSpecific Possible Cause: $probCause";
        }

        switch ($alarmSeverity) {
            case 'cleared':
                $severity = 1;
                break;
            case 'critical':
                $severity = 5;
                break;
            case 'major':
                $severity = 5;
                break;
            case 'minor':
                $severity = 4;
                break;
            case 'warning':
                $severity = 4;
                break;
            case 'indeterminate':
                $severity = 0;
                break;
            default:
                $severity = 2;
                break;
        }

        Log::event($msg, $device->device_id, 'trap', $severity);
    }
}
