<?php

/**
 * AviatIetfAlarm.php
 *
 * Aviat WTM 4000 (AOS 6.x) alarm state-change trap.
 *
 * AOS 6.x replaced the 2.11 alarm model wholesale: AVIAT-ALARM-REPORTING-MIB at
 * .1.3.6.1.4.1.2509.9.47 is gone and AVIAT-IETF-ALARMS-MIB at .1.3.6.1.4.1.2509.9.49
 * (RFC 8632 lineage) took its place. Every legacy trap OID under .47.0.* therefore
 * stops matching silently on upgraded radios.
 *
 * Traps are the only sub-second-fidelity signal available from these radios; SNMP
 * polling runs on a 2 minute cycle and cannot resolve a transient event.
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
 */

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Enum\Severity;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;

class AviatIetfAlarm implements SnmptrapHandler
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
        $severity = $trap->getOidData($trap->findOid('AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmPerceivedSeverity'));
        $resource = $trap->getOidData($trap->findOid('AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmResource'));
        $text = $trap->getOidData($trap->findOid('AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmText'));
        $cause = $trap->getOidData($trap->findOid('AVIAT-IETF-ALARMS-MIB::aviatIetfAlarmProbableCauseString'));

        // ItuPerceivedSeverity. Note the integer ordering is inverted relative to
        // intuition - critical(3) is worse than warning(6) - so this is an explicit
        // map rather than a comparison. snmptrapd may deliver either the label or
        // the raw integer depending on whether the MIB resolved, so accept both.
        $logSeverity = match ((string) $severity) {
            'critical', '3' => Severity::Error,
            'major', '4' => Severity::Warning,
            'minor', '5' => Severity::Notice,
            'warning', '6' => Severity::Notice,
            'cleared', '1' => Severity::Ok,
            default => Severity::Info,
        };

        $message = 'Aviat alarm';
        if ($resource) {
            $message .= " on $resource";
        }
        if ($text) {
            $message .= ": $text";
        }
        if ($severity) {
            $message .= " (severity: $severity)";
        }
        if ($cause && $cause !== $text) {
            $message .= " [cause: $cause]";
        }

        $trap->log($message, $logSeverity);
    }
}
