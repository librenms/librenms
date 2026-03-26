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
 * @copyright  2023 Transiitiv Technologies Ltd.
 * @author     Adam Sweet <adam.sweet@transitiv.co.uk> https://www.transitiv.co.uk/
 */

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Enum\Severity;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;

class CiscoConfigManEvent implements SnmptrapHandler
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
        $EventID = explode('.', $trap->findOid('CISCO-CONFIG-MAN-MIB::ciscoConfigManEvent'));

        $CommandSource = $trap->getOidData($trap->findOid('CISCO-CONFIG-MAN-MIB::ccmHistoryEventCommandSource.' . $EventID[0]));
        $ConfigSource = $trap->getOidData($trap->findOid('CISCO-CONFIG-MAN-MIB::ccmHistoryEventConfigSource.' . $EventID[0]));
        $ConfigDest = $trap->getOidData($trap->findOid('CISCO-CONFIG-MAN-MIB::ccmHistoryEventConfigDestination.' . $EventID[0]));

        $trap->log("A configuration management event was triggered via $CommandSource from config source $ConfigSource to config destination $ConfigDest", Severity::Info);
    }
}
