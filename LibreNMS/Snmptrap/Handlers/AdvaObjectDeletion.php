<?php
/**
 * AdvaObjectDeletion.php
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
 * Traps when Adva objects are deleted. This includes Remote User Login object,
 * Flow Deletion object, LAG Member Port Removed object, and Lag Deletion object.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2018 KanREN, Inc
 * @author     Heath Barnhart <hbarnhart@kanren.net> & Neil Kahle <nkahle@kanren.net>
 */

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;

class AdvaObjectDeletion implements SnmptrapHandler
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
        if ($trap_oid = $trap->findOid('CM-SECURITY-MIB::cmSecurityUserName')) {
            $UserName = $trap->getOidData($trap_oid);
            $trap->log("User object $UserName deleted");
        } elseif ($trap_oid = $trap->findOid('CM-FACILITY-MIB::cmFlowIndex')) {
            $flowID = str_replace('.', '-', substr($trap_oid, 29));
            $trap->log("Flow $flowID deleted");
        } elseif ($trap_oid = $trap->findOid('F3-LAG-MIB::f3LagPortIndex')) {
            $lagPortID = $trap->getOidData($trap_oid);
            $lagID = str_replace('.', '-', substr($trap_oid, -5, 3));
            $trap->log("LAG member port $lagPortID removed from LAG $lagID");
        } elseif ($trap_oid = $trap->findOid('F3-LAG-MIB::f3LagIndex')) {
            $lagID = $trap->getOidData($trap_oid);
            $trap->log("LAG $lagID deleted");
        }
    }
}
