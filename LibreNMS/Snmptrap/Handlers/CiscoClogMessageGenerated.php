<?php

/**
 * CiscoClogMessageGenerated
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
 * @copyright  2023 Transitiv Technologies Ltd. <info@transitiv.co.uk>
 * @author     Adam Sweet <adam.sweet@transitiv.co.uk>
 */

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Enum\Severity;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;

class CiscoClogMessageGenerated implements SnmptrapHandler
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
        $EventID = explode('.', $trap->findOid('CISCO-SYSLOG-MIB::clogMessageGenerated'));

        $Facility = $trap->getOidData($trap->findOid('CISCO-SYSLOG-MIB::clogHistFacility'));
        $Name = $trap->getOidData($trap->findOid('CISCO-SYSLOG-MIB::clogHistMsgName.' . $EventID[0]));
        $CiscoSeverity = $trap->getOidData($trap->findOid('CISCO-SYSLOG-MIB::clogHistSeverity.' . $EventID[0]));
        $MsgTxt = $trap->getOidData($trap->findOid('CISCO-SYSLOG-MIB::clogHistMsgText.' . $EventID[0]));

        // Match Cisco Syslog trap severity levels to LibreNMS eventlog colours
        $SeverityColour = match ($CiscoSeverity) {
            'emergency', 'alert', 'critical', 'error' => Severity::Error,
            'warning' => Severity::Warning,
            'notice' => Severity::Notice,
            'info' => Severity::Info,
            default => Severity::Ok,
        };

        // Special cases
        // Set LibreNMS Eventlog severity colour to green for Link Up
        if ($Facility == 'LINK' && $Name == 'UPDOWN' && $CiscoSeverity == 'error' && str_ends_with($MsgTxt, 'changed state to up')) {
            $Name = 'UP';
            $SeverityColour = Severity::Ok;
        }
        if ($Facility == 'LINK' && $Name == 'UPDOWN' && $CiscoSeverity == 'error' && str_ends_with($MsgTxt, 'changed state to down')) {
            $Name = 'DOWN';
        }

        $trap->log("Cisco Syslog Trap: $Facility $Name: $MsgTxt", $SeverityColour);
    }
}
