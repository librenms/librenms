<?php

/**
 * CiscoUnifiedComputingCucsFaultActiveNotif.php
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
 * @copyright  2025 Transitiv Technologies Ltd. <info@transitiv.co.uk>
 * @author     Adam Sweet <adam.sweet@transitiv.co.uk>
 */

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Enum\Severity;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;

class CiscoUnifiedComputingCucsFaultActiveNotif implements SnmptrapHandler
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
        $FaultIndex = $trap->getOidData($trap->findOid('CISCO-UNIFIED-COMPUTING-MIB::cucsFaultIndex'));
        $Desc = $trap->getOidData($trap->findOid('CISCO-UNIFIED-COMPUTING-MIB::cucsFaultDescription'));
        $Obj = $trap->getOidData($trap->findOid('CISCO-UNIFIED-COMPUTING-MIB::cucsFaultAffectedObjectDn'));
        $Ctime = $trap->getOidData($trap->findOid('CISCO-UNIFIED-COMPUTING-MIB::cucsFaultCreationTime'));
        $Mtime = $trap->getOidData($trap->findOid('CISCO-UNIFIED-COMPUTING-MIB::cucsFaultLastModificationTime'));
        $Cause = $trap->getOidData($trap->findOid('CISCO-UNIFIED-COMPUTING-MIB::cucsFaultProbableCause'));
        $Severity = $trap->getOidData($trap->findOid('CISCO-UNIFIED-COMPUTING-MIB::cucsFaultSeverity'));

        // Match cucsFaultSeverity to LibreNMS eventlog colours
        $SeverityColour = match ($Severity) {
            'critical', 'major' => Severity::Error,
            'minor', 'warning' => Severity::Warning,
            'info' => Severity::Info,
            'cleared' => Severity::Ok,
            default => Severity::Notice,
        };

        $trap->log("Cisco Unified Computing Fault $FaultIndex Active: $Desc for $Obj started at $Ctime, last updated at $Mtime. Probable cause: $Cause", $SeverityColour);
    }
}
