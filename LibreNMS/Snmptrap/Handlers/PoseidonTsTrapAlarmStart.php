<?php
/**
 * PoseidonTsTrapAlarmStart.php
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

class PoseidonTsTrapAlarmStart implements SnmptrapHandler
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
        $oid = $trap->findOid('POSEIDON-MIB::tsAlarmId');
        $id = substr($oid, strlen($oid) + 1);
        $AlarmID = $trap->getOidData($trap->findOid('POSEIDON-MIB::tsAlarmId.' . $id));
        $AlarmDescr = $trap->getOidData($trap->findOid('POSEIDON-MIB::tsAlarmDescr.' . $id));

        $trap->log("Poseidon Alarm Start: Alarm ID $AlarmID: $AlarmDescr. Check the following Poseidon Alarm State Change trap for details", Severity::Warning);
    }
}
