<?php
/**
 * AdvaStateChangeTrap.php
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
 * Takes traps for interface state changes on Adva Ethernet Devices.
 * On an interface state change serveral traps (6 observed) are sent via
 * CM-SYSTEM-MIB::cmStateChangeTrap. This handler creates log entries based
 * on the unit that sent the trap.
 *
 * @link       https://www.librenms.org
 * @copyright  2018 Heath Barnhart
 * @author     Heath Barnhart <hbarnhart@kanren.net> & Neil Kahle <nkahle@kanren.net>
 */

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;
use Log;

class AdvaStateChangeTrap implements SnmptrapHandler
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
        if ($trap_oid = $trap->findOid('CM-FACILITY-MIB::cmEthernetAccPortAdminState')) {
            $adminState = $trap->getOidData($trap_oid);
            $opState = $trap->getOidData($trap->findOid('CM-FACILITY-MIB::cmEthernetAccPortOperationalState'));
            $portName = $trap->getOidData($trap->findOid('IF-MIB::ifName'));
            Log::event("Port state change: $portName Admin State: $adminState Operational State: $opState", $device->device_id, 'trap', 2);
        } elseif ($trap_oid = $trap->findOid('CM-FACILITY-MIB::cmFlowAdminState')) {
            $adminState = $trap->getOidData($trap_oid);
            $opState = $trap->getOidData($trap->findOid('CM-FACILITY-MIB::cmFlowOperationalState'));
            $flowID = substr($trap->findOid('CM-FACILITY-MIB::cmFlowAdminState'), 34);
            $flowID = str_replace('.', '-', $flowID);
            Log::event("Flow state change: $flowID Admin State: $adminState Operational State: $opState", $device->device_id, 'trap', 2);
        } elseif ($trap_oid = $trap->findOid('CM-FACILITY-MIB::cmEthernetNetPortAdminState')) {
            $adminState = $trap->getOidData($trap_oid);
            $opState = $trap->getOidData($trap->findOid('CM-FACILITY-MIB::cmEthernetNetPortOperationalState'));
            $portName = $trap->getOidData($trap->findOid('IF-MIB::ifName'));
            Log::event("Port state change: $portName Admin State: $adminState Operational State: $opState", $device->device_id, 'trap', 2);
        }
    }
}
