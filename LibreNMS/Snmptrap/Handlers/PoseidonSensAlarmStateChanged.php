<?php
/**
 * PoseidonSensAlarmStateChanged.php
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
 * @copyright  2024 Transitiv Technologies Ltd. <info@transitiv.co.uk>
 * @author     Adam Sweet <adam.sweet@transitiv.co.uk>
 */

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Enum\Severity;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;

class PoseidonSensAlarmStateChanged implements SnmptrapHandler
{
    /**
     * Handle snmptrap.
     * Data is pre-parsed and delivered as a Trap.
     *
     * @param  Device  $device
     * @param  Trap  $trap
     * @return void
     */
    public function handle(Device $device, Trap $trap)
    {
        $oid = $trap->findOid('POSEIDON-MIB::sensName');
        $id = substr($oid, strlen($oid) + 1);

        $SensorName = $trap->getOidData($trap->findOid('POSEIDON-MIB::sensName.' . $id));
        $SensorState = $trap->getOidData($trap->findOid('POSEIDON-MIB::sensState.' . $id));
        $RawSensorValue = $trap->getOidData($trap->findOid('POSEIDON-MIB::sensValue.' . $id));
        $SensorUnit = $trap->getOidData($trap->findOid('POSEIDON-MIB::sensUnit.' . $id));
        $SensorValue = (int) $RawSensorValue / 10;

        // Match Poseidon sensor states to LibreNMS eventlog colours
        switch ($SensorState) {
            case 'invalid':
                $State = 'invalid';
                $SeverityColour = Severity::Warning; // yellow
                break;
            case 'normal':
                $State = 'normal';
                $SeverityColour = Severity::Ok; // green
                break;
            case 'alarmstate':
                $State = 'alarmstate';
                $SeverityColour = Severity::Error; // red
                break;
            case 'alarm':
                $State = 'alarm';
                $SeverityColour = Severity::Error; // red
                break;
            default:
                $State = 'unknown';
                $SeverityColour = Severity::Warning; // yellow
        }
        $trap->log("Poseidon Sensor State Change: $SensorName changed state to $State: $SensorValue $SensorUnit", $SeverityColour);
    }
}
