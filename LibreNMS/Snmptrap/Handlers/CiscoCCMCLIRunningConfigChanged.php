<?php

/**
 * CiscoCCMCLIRunningConfigChanged
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

class CiscoCCMCLIRunningConfigChanged implements SnmptrapHandler
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
        $EventID = explode('.', $trap->findOid('CISCO-CONFIG-MAN-MIB::ccmCLIRunningConfigChanged'));
        $ChangeTime = $trap->getOidData($trap->findOid('CISCO-CONFIG-MAN-MIB::ccmHistoryRunningLastChanged.' . $EventID[0]));
        $TermType = $trap->getOidData($trap->findOid('CISCO-CONFIG-MAN-MIB::ccmHistoryEventTerminalType.' . $EventID[0]));
        $trap->log("The running config was changed at system uptime $ChangeTime from terminal type $TermType", Severity::Info);
    }
}
