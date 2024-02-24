<?php
/**
 * ApcPduNearOverload.php
 *
 * -Description-
 *
 * APC PDU nearing over current level.
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
 * Traps when Adva objects are created. This includes Remote User Login object,
 * Flow Creation object, and LAG Creation object.
 *
 * @link       http://librenms.org
 *
 * @copyright  2022 KanREN, Inc
 * @author     Heath Barnhart <hbarnhart#kanren.net>
 */

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Enum\Severity;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;

class ApcPduNearOverload implements SnmptrapHandler
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
        //Get the PDU's name, affected phase, and the alarm string
        $pdu_id = ApcTrapUtil::getPduIdentName($trap);
        $phase_num = ApcTrapUtil::getPduPhaseNum($trap);
        $alarm_str = ApcTrapUtil::getApcTrapString($trap);
        $trap->log("$pdu_id phase $phase_num $alarm_str", Severity::Warning);
    }
}
